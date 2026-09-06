import { test, expect } from '../fixtures/auth';

test.describe('Functional application flows', () => {
  test('dashboard renders seeded activity metrics', async ({ authenticatedPage: page }) => {
    await page.goto('/dashboard');

    await expect(page).toHaveURL(/\/dashboard/);
    await expect(page.locator('body')).toContainText('Укупно студената');
    await expect(page.locator('body')).toContainText('Положени испити');
    await expect(page.locator('body')).toContainText('Пријављени испити');
  });

  test('authenticated admin sees seeded candidates', async ({ authenticatedPage: page }) => {
    await page.goto('/student/index/1');

    await expect(page).toHaveURL(/\/student\/index\/1/);
    await expect(page.locator('body')).toContainText('Petar');
    await expect(page.locator('body')).toContainText('001/2025');
  });

  test('candidate registration form exposes required fields and validation', async ({ authenticatedPage: page }) => {
    await page.goto('/kandidat/create');

    await expect(page.locator('form:has(input[name="page"][value="1"])')).toBeVisible();
    await expect(page.locator('input[name="ImeKandidata"]')).toBeVisible();
    await expect(page.locator('input[name="PrezimeKandidata"]')).toBeVisible();
    await expect(page.locator('input[name="ImeKandidata"]')).toHaveAttribute('required', '');
    await expect(page.locator('input[name="PrezimeKandidata"]')).toHaveAttribute('required', '');
  });

  test('admin can complete the two-step candidate registration flow', async ({ authenticatedPage: page }) => {
    const uniqueJmbg = `99${Date.now()}`.slice(-13);
    const candidateName = `E2E${Date.now()}`;
    let candidateId: string | undefined;

    try {
      await page.goto('/kandidat/create');
      const programSelect = page.locator('select[name="StudijskiProgram"]');
      await programSelect.selectOption({ index: 1 });
      await page.locator('select[name="SkolskeGodineUpisa"]').selectOption({ index: 1 });
      await page.locator('input[name="ImeKandidata"]').fill(candidateName);
      await page.locator('input[name="PrezimeKandidata"]').fill('Testovic');
      await page.locator('input[name="JMBG"]').fill(uniqueJmbg);

      await page.locator('form:has(input[name="page"][value="1"]) button[type="submit"]').click();
      candidateId = await page.locator('input[name="insertedId"]').inputValue();
      await expect(page.locator('input[name="page"][value="2"]')).toBeAttached();

      for (const grade of ['prvi', 'drugi', 'treci', 'cetvrti']) {
        await page.locator(`select[name="${grade}Razred"]`).selectOption({ index: 1 });
      }
      for (const grade of ['1', '2', '3', '4']) {
        await page.locator(`input[name="SrednjaOcena${grade}"]`).fill('4');
      }
      await page.locator('select[name="OpstiUspehSrednjaSkola"]').selectOption({ index: 1 });
      await page.locator('input[name="SrednjaOcenaSrednjaSkola"]').fill('4');

      await page.locator('form:has(input[name="page"][value="2"]) button[type="submit"]').click();
      await page.goto(`/kandidat/${candidateId}`);
      await expect(page).toHaveURL(new RegExp(`/kandidat/${candidateId}$`));
      await expect(page.locator('body')).toContainText(candidateName);
    } finally {
      if (candidateId) {
        await page.goto(`/kandidat/${candidateId}/delete`);
      }
    }
  });

  test('admin can open a seeded subject and see exam registrations', async ({ authenticatedPage: page }) => {
    await page.goto('/predmeti/');

    const subjectLink = page.locator('a[href*="prijava/zaPredmet/"]').first();
    await expect(subjectLink).toBeVisible();
    await subjectLink.click();

    await expect(page).toHaveURL(/\/prijava\/zaPredmet\/\d+/);
    await expect(page.locator('body')).toContainText('Пријава испита');
    await expect(page.locator('body')).toContainText('Petar');
  });

  test('exam-record form loads registered students through AJAX', async ({ authenticatedPage: page }) => {
    await page.goto('/zapisnik/create');

    await expect(page.locator('form[action*="/zapisnik/storeZapisnik"]')).toBeVisible();
    await expect(page.locator('#rok_id option')).not.toHaveCount(0);
    await expect(page.locator('#predmet_id option')).not.toHaveCount(0);
    await expect(page.locator('#profesor_id option')).not.toHaveCount(0);

    const responsePromise = page.waitForResponse((response) =>
      response.url().includes('/zapisnik/vratiZapisnikStudenti') && response.request().method() === 'GET'
    );
    await page.locator('#ajaxSubmitPrijava').click();
    const response = await responsePromise;
    expect(response.ok()).toBeTruthy();
    const payload = await response.json();
    expect(payload).toHaveProperty('kandidati');
    expect(payload).toHaveProperty('message');
    expect(payload).toHaveProperty('prijavaId');
  });

  test('admin can create and then remove a zapisnik student', async ({ authenticatedPage: page }) => {
    await page.goto('/zapisnik/create');
    await page.locator('#ajaxSubmitPrijava').click();
    await expect(page.locator('input[name^="odabir["]')).not.toHaveCount(0);

    await page.locator('#vreme').fill('10:00');
    await page.locator('#ucionica').fill('Sala E2E');
    await page.locator('form[action*="/zapisnik/storeZapisnik"] button[type="submit"]').click();
    await expect(page).toHaveURL(/\/zapisnik\/?$/);

    const deleteLink = page.locator('a[href*="/zapisnik/"][href*="/delete"]').last();
    await expect(deleteLink).toBeVisible();
    page.once('dialog', (dialog) => dialog.accept());
    await deleteLink.click();
    await expect(page).toHaveURL(/\/zapisnik(\/)?$/);
  });

  test('admin can save a grade from the zapisnik form', async ({ authenticatedPage: page }) => {
    await page.goto('/zapisnik');
    await page.locator('a[href^="/zapisnik/pregled/"]').first().click();

    const gradeForm = page.locator('form[action*="/zapisnik/polozeniIspit"]');
    await expect(gradeForm).toBeVisible();
    await gradeForm.locator('input[name^="brojBodova["]').first().fill('88');
    await gradeForm.locator('select[name^="konacnaOcena["]').first().selectOption('9');
    await gradeForm.locator('select[name^="statusIspita["]').first().selectOption({ index: 1 });
    await gradeForm.locator('button[type="submit"]').click();

    const zapisnikUrl = page.url();
    await expect(page).toHaveURL(/\/zapisnik\/pregled\/\d+/);
    await page.reload();
    await expect(page.locator('form[action*="/zapisnik/polozeniIspit"] input[name^="brojBodova["]').first()).toHaveValue('88');
    await expect(page.locator('form[action*="/zapisnik/polozeniIspit"] select[name^="konacnaOcena["]').first()).toHaveValue('9');
    expect(zapisnikUrl).toMatch(/\/zapisnik\/pregled\/\d+/);
  });

  test('admin can request a zapisnik PDF', async ({ authenticatedPage: page }) => {
    await page.goto('/zapisnik');
    await page.locator('a[href^="/zapisnik/pregled/"]').first().click();

    const printForm = page.locator('form[action*="/izvestaji/zapisnikStampa/"]');
    await expect(printForm).toBeVisible();
    await printForm.evaluate((form) => {
      (form as HTMLFormElement).target = '_self';
    });
    const pdfResponsePromise = page.waitForResponse((response) =>
      response.url().includes('/izvestaji/zapisnikStampa/') && response.request().method() === 'POST'
    );
    await printForm.locator('input[type="submit"]').click();
    const pdfResponse = await pdfResponsePromise;
    expect(pdfResponse.ok()).toBeTruthy();
    expect(pdfResponse.headers()['content-type']).toContain('application/pdf');
  });

  test('bulk exam-registration form exposes the seeded student search', async ({ authenticatedPage: page }) => {
    await page.goto('/predmeti/');

    const subjectLink = page.locator('a[href*="prijava/zaPredmet/"]').first();
    const subjectUrl = await subjectLink.getAttribute('href');
    const subjectId = subjectUrl?.split('/').pop();
    expect(subjectId).toBeTruthy();

    await page.goto(`/prijava/predmetVise/${subjectId}`);
    await expect(page.locator('#formaKandidatiOdabir')).toBeVisible();
    await expect(page.locator('#studentSearch')).toBeVisible();
    await expect(page.locator('#addStudentButton')).toBeVisible();
    await expect(page.locator('#studentSearch')).toHaveAttribute('placeholder', /индекса/);
  });

  test('zapisnik shows enrolled students and available modal candidates', async ({ authenticatedPage: page }) => {
    await page.goto('/zapisnik');

    const pregledLink = page.locator('a[href^="/zapisnik/pregled/"]').first();
    await expect(pregledLink).toBeVisible();
    await pregledLink.click();
    await expect(page).toHaveURL(/\/zapisnik\/pregled\/\d+/);

    const openModalButton = page.locator('button[onclick="openModal(\'myModal\')"]');
    await expect(openModalButton).toBeVisible();
    await openModalButton.click();
    await expect(page.locator('#myModal')).toBeVisible();

    const candidateOptions = page.locator('#addStudentList option:not([value="0"])');
    await expect(candidateOptions).not.toHaveCount(0);
    await expect(candidateOptions.first()).toContainText(/\d{3}\/\d{4} - .+/);

    await candidateOptions.first().evaluate((option) => {
      (option as HTMLOptionElement).selected = true;
    });
    const candidateId = await candidateOptions.first().getAttribute('value');
    const responsePromise = page.waitForResponse((response) =>
      response.url().includes('/prijava/vratiKandidataPoBroju') && response.request().method() === 'POST'
    );
    await page.locator('#addStudentButton').click();
    const response = await responsePromise;
    expect(response.ok()).toBeTruthy();
    await expect(page.locator('#addStudentTableBody input[name="odabir[]"]')).toHaveValue(candidateId ?? '');
  });
});

test.describe('Responsive functional flows', () => {
  test.use({ viewport: { width: 390, height: 844 } });

  test('zapisnik modal remains usable on mobile', async ({ authenticatedPage: page }) => {
    await page.goto('/zapisnik');
    await page.locator('a[href^="/zapisnik/pregled/"]').first().click();

    const openModalButton = page.locator('button[onclick="openModal(\'myModal\')"]');
    await expect(openModalButton).toBeVisible();
    await openModalButton.click();

    await expect(page.locator('#myModal')).toBeVisible();
    await expect(page.locator('#addStudentList option:not([value="0"])')).not.toHaveCount(0);
  });
});

test.describe('Protected reference pages', () => {
  const submenuRoutes = [
    ['#kandidatSubmenu', '/kandidat/create'],
    ['#kandidatSubmenu', '/kandidat'],
    ['#masterSubmenu', '/master/create'],
    ['#masterSubmenu', '/master'],
    ['#studentiSubmenu', '/student/index/1'],
    ['#studentiSubmenu', '/student/index/2'],
    ['#studentiSubmenu', '/student/zamrznuti'],
    ['#studentiSubmenu', '/student/ispisani'],
    ['#studentiSubmenu', '/student/diplomirani'],
    ['#ispitiSubmenu', '/kalendar'],
    ['#ispitiSubmenu', '/predmeti'],
    ['#ispitiSubmenu', '/zapisnik'],
    ['#adminSifarniciSubmenu', '/tipStudija'],
    ['#adminSifarniciSubmenu', '/studijskiProgram'],
    ['#adminSifarniciSubmenu', '/godinaStudija'],
    ['#adminSifarniciSubmenu', '/statusStudiranja'],
    ['#adminSifarniciSubmenu', '/semestar'],
    ['#adminSifarniciSubmenu', '/ispitniRok'],
    ['#adminSifarniciSubmenu', '/oblikNastave'],
    ['#adminSifarniciSubmenu', '/tipPredmeta'],
    ['#adminSifarniciSubmenu', '/bodovanje'],
    ['#adminSifarniciSubmenu', '/statusKandidata'],
    ['#adminSifarniciSubmenu', '/statusIspita'],
    ['#adminSifarniciSubmenu', '/statusProfesora'],
    ['#adminSifarniciSubmenu', '/tipPrijave'],
    ['#sifarniciSubmenu', '/sport'],
    ['#sifarniciSubmenu', '/predmet'],
    ['#sifarniciSubmenu', '/profesor'],
    ['#sifarniciSubmenu', '/krsnaSlava'],
    ['#sifarniciSubmenu', '/region'],
    ['#sifarniciSubmenu', '/opstina'],
    ['#nastavaSubmenu', '/raspored'],
    ['#nastavaSubmenu', '/aktivnost'],
    ['#komunikacijaSubmenu', '/obavestenja'],
    ['#komunikacijaSubmenu', '/moja-obavestenja'],
    ['#analitikaSubmenu', '/dashboard'],
  ] as const;

  for (const [submenu, path] of submenuRoutes) {
    test(`menu state is correct for ${path}`, async ({ authenticatedPage: page }) => {
      await page.goto(path);

      const activeSubmenu = page.locator(submenu);
      await expect(activeSubmenu).toBeVisible();
      const linkCount = await activeSubmenu.locator('a').count();
      let foundActiveLink = false;
      for (let index = 0; index < linkCount; index++) {
        const link = activeSubmenu.locator('a').nth(index);
        const href = await link.getAttribute('href');
        if (href && new URL(href, page.url()).pathname.replace(/\/$/, '') === path.replace(/\/$/, '')) {
          await expect(link).toHaveClass(/active/);
          foundActiveLink = true;
          break;
        }
      }
      expect(foundActiveLink).toBeTruthy();

      for (const otherSubmenu of [
        '#kandidatSubmenu', '#masterSubmenu', '#studentiSubmenu', '#ispitiSubmenu',
        '#adminSifarniciSubmenu', '#sifarniciSubmenu', '#nastavaSubmenu',
        '#komunikacijaSubmenu', '#analitikaSubmenu',
      ]) {
        if (otherSubmenu !== submenu) {
          await expect(page.locator(otherSubmenu)).toBeHidden();
        }
      }
    });
  }

  test('selected submenu item remains highlighted', async ({ authenticatedPage: page }) => {
    await page.goto('/sport');

    await expect(page.locator('#sifarniciSubmenu')).toBeVisible();
    await expect(page.locator('#sifarniciSubmenu a[href$="/sport"]')).toHaveClass(/active/);
    await expect(page.locator('#sifarniciSubmenu').locator('xpath=..')).not.toHaveClass(/(^|\s)active(\s|$)/);
  });

  test('switching submenu routes closes the old menu and highlights the new item', async ({ authenticatedPage: page }) => {
    await page.goto('/sport');
    await expect(page.locator('#sifarniciSubmenu')).toBeVisible();
    await expect(page.locator('#adminSifarniciSubmenu')).toBeHidden();
    await expect(page.locator('#sifarniciSubmenu a[href$="/sport"]')).toHaveClass(/active/);

    await page.goto('/tipStudija');
    await expect(page.locator('#adminSifarniciSubmenu')).toBeVisible();
    await expect(page.locator('#sifarniciSubmenu')).toBeHidden();
    await expect(page.locator('#adminSifarniciSubmenu a[href$="/tipStudija"]')).toHaveClass(/active/);

    await page.goto('/statusIspita');
    await expect(page.locator('#adminSifarniciSubmenu')).toBeVisible();
    await expect(page.locator('#adminSifarniciSubmenu a[href$="/statusIspita"]')).toHaveClass(/active/);
  });

  const pages = [
    ['/ispitniRok', 'exam periods'],
    ['/statusIspita', 'exam statuses'],
    ['/statusProfesora', 'professor statuses'],
    ['/tipPrijave', 'application types'],
    ['/tipStudija', 'study types'],
    ['/studijskiProgram', 'study programs'],
    ['/godinaStudija', 'study years'],
    ['/statusStudiranja', 'study statuses'],
    ['/semestar', 'semesters'],
    ['/sport', 'sports'],
    ['/predmet', 'subjects'],
    ['/profesor', 'professors'],
    ['/users', 'users'],
  ] as const;

  for (const [path, label] of pages) {
    test(`${label} page loads successfully`, async ({ authenticatedPage: page }) => {
      const response = await page.goto(path);

      expect(response?.status()).toBe(200);
      await expect(page.locator('body')).not.toContainText('Страница коју тражите не постоји');
    });
  }
});

test.describe('Operational pages', () => {
  const pages = [
    ['/dashboard/ispiti', 'exam analytics'],
    ['/dashboard/studenti', 'student analytics'],
    ['/obavestenja', 'notifications'],
    ['/raspored', 'schedule'],
    ['/aktivnost', 'activities'],
    ['/kandidat/documents/incomplete', 'incomplete documents'],
    ['/student/index/2', 'master students'],
    ['/student/zamrznuti', 'frozen students'],
    ['/student/diplomirani', 'graduated students'],
    ['/student/ispisani', 'withdrawn students'],
    ['/users/create', 'user form'],
    ['/master/', 'master candidates'],
    ['/predmeti/', 'exam-registration subjects'],
  ] as const;

  for (const [path, label] of pages) {
    test(`${label} page loads successfully`, async ({ authenticatedPage: page }) => {
      const response = await page.goto(path);

      expect(response?.status()).toBe(200);
      await expect(page.locator('body')).not.toContainText('Страница коју тражите не постоји');
    });
  }
});
