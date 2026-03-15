import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext();
  const page = await context.newPage();
  
  page.on('console', msg => {
    if (msg.type() === 'error') {
      console.log('Console error:', msg.text());
    }
  });
  
  await page.goto('http://localhost:8000/login');
  await page.fill('input[name="email"]', 'fzs@fzs.rs');
  await page.fill('input[name="password"]', 'fzs123');
  await page.click('button[type="submit"]');
  await page.waitForTimeout(2000);
  
  await page.goto('http://localhost:8000/zapisnik/create');
  await page.waitForTimeout(3000);
  
  const ajaxButton = await page.$('#ajaxSubmitPrijava');
  const addStudentButton = await page.$('#addStudentLink');
  
  console.log('Buttons exist - ajaxSubmitPrijava:', !!ajaxButton, 'addStudentLink:', !!addStudentButton);
  
  if (ajaxButton) {
    await ajaxButton.click();
    await page.waitForTimeout(3000);
    const tableContent = await page.$eval('#tabela tbody', el => el.innerHTML);
    console.log('Table has content:', tableContent.length > 0);
    console.log('Table content:', tableContent.substring(0, 300));
  }
  
  await browser.close();
})();
