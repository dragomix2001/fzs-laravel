# FZS-Laravel: Preostala Poboljšanja

Ovaj dokument opisuje preostale arhitektonske i funkcionalnosti poboljšanja za FZS-Laravel aplikaciju nakon uspešno završenih Faza 1, 2 i prioritetnih poboljšanja.

**Trenutna ocena kvaliteta: 9.0+/10**

---

## ✅ Završeno (Faza 1 & 2)

### Faza 1 — Brze Optimizacije
- [x] **1.1** Ispravljeno N+1 query problema u IspitController
- [x] **1.2** Ispravljeno N+1 query problema u PrijavaController
- [x] **1.3** Ispravljeno N+1 query problema u NotificationService
- [x] **1.4** Ispravljeno N+1 query problema u KandidatController
- [x] **1.5** Publikovane Sanctum i Telescope migracije
- [x] **1.6** Dodat PHPStan level 5 u CI
- [x] **1.7** Prebačene file operacije na Storage facade

### Faza 2 — Arhitektonski Refaktoring (6/6 završeno)
- [x] **2.1** Ekstrahovana business logika KandidatController → KandidatService
- [x] **2.2** Ekstrahovana business logika IspitController → IspitService
- [x] **2.3** Ekstrahovane static metode UpisGodine → UpisService
- [x] **2.4** Standardizovane validacije na Form Request klase
- [x] **2.5** IspitPdfService ekstrakcija iz IspitService (821→614 LOC)
- [x] **2.6** KandidatEnrollmentService ekstrakcija iz KandidatService

## ✅ Završeno — Prioritetna Poboljšanja (April 2026)

### P1: Refaktor PrijavaController (731→280 LOC)
- [x] Ekstrahovana kompletna business logika u PrijavaService (849 LOC)
- [x] PrijavaController sveden na thin HTTP sloj
- [x] Svi testovi prolaze bez regresije

### P2: PHPStan baseline potpuno eliminisan (40→0 grešaka)
- [x] Ispravljeno 40+ grešaka: env→config, PHPDoc tipovi, class reference, return tipovi
- [x] Popravljen TCPDF konstruktor (višak parametra)
- [x] Kreiran PHPStan stub za TCPDF
- [x] Ispravljen unsafe `new static()` u Auditable trait-u
- [x] Ispravljene ZipArchive metode u BackupService
- [x] Baseline neon fajl potpuno prazan — 0 ignoreErrors entry-a

### P3: Dodato 9 novih FormRequest klasa (22→31 ukupno)
- [x] StoreRasporedRequest, StoreObavestenjeRequest, UpdateObavestenjeRequest
- [x] StoreUserRequest, UpdateUserRequest
- [x] ChatMessageRequest, QuickQuestionRequest
- [x] ImportFileRequest, LoginRequest
- [x] 5 kontrolera ažurirano da koriste FormRequest klase

### P4: Poboljšane test asercije (assertTrue(true) → pravi testovi)
- [x] 23 smoke testa zamenjeno pravim asercijama
- [x] PDF output validacija (%PDF magic bytes) za StudentListService
- [x] Guest redirect asercije za AuthTest i ComprehensiveFeatureTest
- [x] Route access testovi za BusinessFlowTest
- [x] Asercije: 3404→3426 (+22)

### P5: DRY StudentListService (408→323 LOC)
- [x] Ekstrahovan zajednički `generatePdf()` metod u BasePdfService
- [x] Refaktorisano svih 12 PDF metoda da koriste shared metod
- [x] 85 linija uklonjeno (21% smanjenje)

## ✅ Završeno — Brza Poboljšanja (14. april 2026)

### B1: Named routes + ispravka baga u KandidatController
- [x] Zamenjeno ~15 hardcoded `redirect('/kandidat/...')` poziva sa `redirect()->route()` helperom
- [x] Ispravljen bag na liniji 142: `redirect('/kandidat?studijskiProgramId=1'.$id)` — string `'1'` se greškom konkatenirao ispred `studijskiProgram_id`
- [x] Dodato `->name()` na 16 route definicija u `app/Http/routes.php` (kandidat.sport, master.index, master.edit, student.index, itd.)

### B2: Popravljen mrtav Excel export kod u IzvestajiController
- [x] Metoda `excelStampa()` koristila Maatwebsite v2 API (`Excel::create()`) koji ne postoji u v4
- [x] Prepisano na Maatwebsite v4 API sa `Excel::download()`
- [x] Kreirana nova `SpisakKandidataExport` klasa (FromCollection + WithHeadings)
- [x] Uklonjenja `@phpstan-ignore` anotacija

### B3: Error logging u IspitController
- [x] Dodato `Log::error()` sa SQL i bindings kontekstom u 2 catch bloka (`storeZapisnik` i `dodajStudenta`)
- [x] Usklađeno sa postojećim patternom iz SportskoAngazovanjeController

### B4: Thin KandidatController i IzvestajiController (14. april 2026)
- [x] KandidatController query/view assembly prebačen u KandidatService helpere
- [x] IzvestajiController prebačen na constructor injection
- [x] Dodata validacija izveštaja sa 5 novih FormRequest klasa: ReportGodinaRequest, ReportPredmetRequest, ReportProgramGodinaRequest, ReportProgramRequest, KandidatSportRequest
- [x] Zamenjeni raw `request->all()` sportski upisi validiranim payloadom
- [x] Dodata FormRequest validacija za bulk kandidat akcije
- [x] KandidatController: smanjen sa ~400 na 286 LOC

### Trenutno stanje nakon svih poboljšanja:
- **Testovi**: 1519 testova, 3836 asercija, 0 grešaka
- **Test coverage**: 71.51% linija (4394/6145) — cilj od 70%+ dostignut
- **PHPStan**: Level 5, 0 grešaka, prazan baseline
- **Pint**: pass
- **CI/CD**: Oba pipeline-a zelena (Laravel CI/CD + CodeQL)
- **FormRequest klase**: 30 ukupno (od početnih 22)
- **Service klase**: 21 (KandidatService, IspitService, PrijavaService, UpisService, IspitPdfService, KandidatEnrollmentService, BasePdfService, StudentListService, itd.)
- **Export klase**: 4 (KandidatiExport, PolozeniIspitiExport, SpisakKandidataExport, StudentiExport)

---

## ✅ Završeno — Arhitektonska Faza (April 2026)

### P6: Ekstrakcija DiplomskiPrijavaService
- [x] `DiplomskiPrijavaService` kreiran sa 15 metoda (tema, odbrana, polaganje)
- [x] `PrijavaService` smanjen sa 849 na ~540 LOC
- [x] `PrijavaController` ažuriran sa novim service injektovanjem
- [x] 23 feature testa u `DiplomskiPrijavaServiceTest.php` (svi prolaze)
- [x] PHPStan 0 grešaka, Pint clean, CI zeleno

## ✅ Završeno — Infrastrukturna Faza

### FAZA 2.5: Dodavanje Foreign Key Constraints

**Status:** ✅ Završeno  
**Prioritet:** MEDIUM  
**Procenjeno vreme:** 3-4 sata  
**Rizik:** SREDNJI (može zahtevati čišćenje podataka)

#### Opis
Trenutno baza podataka **nema foreign key constrainte** na većini relacija, što omogućava:
- Orphaned zapise (kandidati bez validnih programa, ispiti bez validnih studenata)
- Gubitak integriteta podataka
- Teže održavanje

#### Akcija

**1. Pre-migracija analiza:**
```bash
# Prvo proveriti postojeće orphaned zapise
php artisan tinker

# Provera kandidata sa nepostojećim studijskim programom
Kandidat::whereNotIn('studijskiProgram_id', StudijskiProgram::pluck('id'))->count();

# Provera prijava ispita sa nepostojećim kandidatom
PrijavaIspita::whereNotIn('kandidat_id', Kandidat::pluck('id'))->count();

# Provera UpisGodine sa nepostojećim kandidatom
UpisGodine::whereNotIn('kandidat_id', Kandidat::pluck('id'))->count();
```

**2. Kreiranje migracije:**
```bash
php artisan make:migration add_foreign_key_constraints_to_core_tables
```

**3. Definisanje constraints u migraciji:**

```php
// database/migrations/YYYY_MM_DD_HHMMSS_add_foreign_key_constraints_to_core_tables.php

public function up()
{
    Schema::table('kandidat', function (Blueprint $table) {
        $table->foreign('studijskiProgram_id')
              ->references('id')->on('studijski_program')
              ->onDelete('restrict');
        
        $table->foreign('tipStudija_id')
              ->references('id')->on('tip_studija')
              ->onDelete('restrict');
        
        $table->foreign('skolskaGodinaUpisa_id')
              ->references('id')->on('skolska_god_upisa')
              ->onDelete('restrict');
        
        $table->foreign('statusUpisa_id')
              ->references('id')->on('status_studiranja')
              ->onDelete('restrict');
    });

    Schema::table('upis_godine', function (Blueprint $table) {
        $table->foreign('kandidat_id')
              ->references('id')->on('kandidat')
              ->onDelete('cascade');
        
        $table->foreign('studijskiProgram_id')
              ->references('id')->on('studijski_program')
              ->onDelete('restrict');
        
        $table->foreign('tipStudija_id')
              ->references('id')->on('tip_studija')
              ->onDelete('restrict');
        
        $table->foreign('statusGodine_id')
              ->references('id')->on('status_godine')
              ->onDelete('restrict');
    });

    Schema::table('prijava_ispita', function (Blueprint $table) {
        $table->foreign('kandidat_id')
              ->references('id')->on('kandidat')
              ->onDelete('cascade');
        
        $table->foreign('predmet_id')
              ->references('id')->on('predmet')
              ->onDelete('restrict');
        
        $table->foreign('profesor_id')
              ->references('id')->on('profesor')
              ->onDelete('restrict');
        
        $table->foreign('rok_id')
              ->references('id')->on('aktivni_ispitni_rokovi')
              ->onDelete('restrict');
    });

    Schema::table('polozeni_ispiti', function (Blueprint $table) {
        $table->foreign('kandidat_id')
              ->references('id')->on('kandidat')
              ->onDelete('cascade');
        
        $table->foreign('predmet_id')
              ->references('id')->on('predmet')
              ->onDelete('restrict');
        
        $table->foreign('prijava_id')
              ->references('id')->on('prijava_ispita')
              ->onDelete('cascade');
        
        $table->foreign('zapisnik_id')
              ->references('id')->on('zapisnik_o_polaganju_ispita')
              ->onDelete('set null');
    });

    Schema::table('zapisnik_o_polaganju_ispita', function (Blueprint $table) {
        $table->foreign('predmet_id')
              ->references('id')->on('predmet')
              ->onDelete('restrict');
        
        $table->foreign('profesor_id')
              ->references('id')->on('profesor')
              ->onDelete('restrict');
        
        $table->foreign('rok_id')
              ->references('id')->on('aktivni_ispitni_rokovi')
              ->onDelete('restrict');
    });

    Schema::table('zapisnik_o_polaganju__student', function (Blueprint $table) {
        $table->foreign('zapisnik_id')
              ->references('id')->on('zapisnik_o_polaganju_ispita')
              ->onDelete('cascade');
        
        $table->foreign('kandidat_id')
              ->references('id')->on('kandidat')
              ->onDelete('cascade');
    });

    Schema::table('zapisnik_o_polaganju__studijski_program', function (Blueprint $table) {
        $table->foreign('zapisnik_id')
              ->references('id')->on('zapisnik_o_polaganju_ispita')
              ->onDelete('cascade');
        
        $table->foreign('studijskiProgram_id')
              ->references('id')->on('studijski_program')
              ->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('zapisnik_o_polaganju__studijski_program', function (Blueprint $table) {
        $table->dropForeign(['zapisnik_id']);
        $table->dropForeign(['studijskiProgram_id']);
    });

    Schema::table('zapisnik_o_polaganju__student', function (Blueprint $table) {
        $table->dropForeign(['zapisnik_id']);
        $table->dropForeign(['kandidat_id']);
    });

    Schema::table('zapisnik_o_polaganju_ispita', function (Blueprint $table) {
        $table->dropForeign(['predmet_id']);
        $table->dropForeign(['profesor_id']);
        $table->dropForeign(['rok_id']);
    });

    Schema::table('polozeni_ispiti', function (Blueprint $table) {
        $table->dropForeign(['kandidat_id']);
        $table->dropForeign(['predmet_id']);
        $table->dropForeign(['prijava_id']);
        $table->dropForeign(['zapisnik_id']);
    });

    Schema::table('prijava_ispita', function (Blueprint $table) {
        $table->dropForeign(['kandidat_id']);
        $table->dropForeign(['predmet_id']);
        $table->dropForeign(['profesor_id']);
        $table->dropForeign(['rok_id']);
    });

    Schema::table('upis_godine', function (Blueprint $table) {
        $table->dropForeign(['kandidat_id']);
        $table->dropForeign(['studijskiProgram_id']);
        $table->dropForeign(['tipStudija_id']);
        $table->dropForeign(['statusGodine_id']);
    });

    Schema::table('kandidat', function (Blueprint $table) {
        $table->dropForeign(['studijskiProgram_id']);
        $table->dropForeign(['tipStudija_id']);
        $table->dropForeign(['skolskaGodinaUpisa_id']);
        $table->dropForeign(['statusUpisa_id']);
    });
}
```

**4. Čišćenje podataka (pre pokretanja migracije):**
```php
php artisan make:command CleanupOrphanedRecords

// app/Console/Commands/CleanupOrphanedRecords.php
public function handle()
{
    $this->info('Cleaning up orphaned records...');
    
    DB::table('kandidat')
        ->whereNull('studijskiProgram_id')
        ->update(['studijskiProgram_id' => 1]);
    
    $this->info('Cleanup complete!');
}
```

**5. Testiranje:**
```bash
mysqldump -u root -p fzs > backup_before_fk_constraints.sql
php artisan migrate
```

#### Benefit
- **Integritet podataka**: Spreči orphaned zapise
- **Sigurnost**: Automatsko čišćenje kaskadnim brisanjem gde je potrebno
- **Dokumentacija**: Foreign keys jasno pokazuju relacije u bazi

---

### FAZA 2.6: Kreiranje Queued Job Klasa

**Status:** ✅ Završeno  
**Prioritet:** MEDIUM  
**Rizik:** NIZAK

#### Implementirano
- `BroadcastNotificationJob` — dispatchovan iz `NotificationService`
- `MassEnrollmentJob` — dispatchovan iz `KandidatEnrollmentService`
- `GenerateZapisnikPdfJob` — dispatchovan iz `IspitPdfService`
- Supervisor config dokumentovan za production deploy

#### Originalni Opis
Trenutno sve operacije rade **sinhrono**, što blokira HTTP zahteve za:
- Slanje notifikacija (može biti 100+ korisnika)
- Generisanje PDF dokumenata (sporo)
- Masovni upis kandidata (petlje sa DB operacijama)

#### Akcija

**1. Kreirati Job klase:**

```bash
php artisan make:job BroadcastNotificationJob
php artisan make:job GenerateZapisnikPdfJob
php artisan make:job MassEnrollmentJob
```

**2. Implementacija `BroadcastNotificationJob`:**

```php
// app/Jobs/BroadcastNotificationJob.php
class BroadcastNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    public function __construct(
        public Obavestenje $obavestenje,
        public array $userIds
    ) {}

    public function handle()
    {
        foreach ($this->userIds as $userId) {
            // Send notification...
        }
    }

    public function failed(\Throwable $exception)
    {
        \Log::error('Notification broadcast failed', [
            'obavestenje_id' => $this->obavestenje->id,
            'error' => $exception->getMessage()
        ]);
    }
}
```

**3. Implementacija `MassEnrollmentJob`:**

```php
// app/Jobs/MassEnrollmentJob.php
class MassEnrollmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 600;

    public function __construct(public array $kandidatIds) {}

    public function handle(UpisService $upisService)
    {
        foreach ($this->kandidatIds as $kandidatId) {
            try {
                $upisService->registrujKandidata($kandidatId);
            } catch (\Exception $e) {
                \Log::error("Mass enrollment failed for kandidat {$kandidatId}", [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
```

**4. Supervisor konfiguracija (za production):**

```ini
# /etc/supervisor/conf.d/fzs-laravel-worker.conf
[program:fzs-laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/dragomix/fzs-laravel/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=dragomix
numprocs=2
redirect_stderr=true
stdout_logfile=/home/dragomix/fzs-laravel/storage/logs/worker.log
stopwaitsecs=3600
```

#### Benefit
- **Brži response**: Korisnici ne čekaju slanje email-ova ili PDF generaciju
- **Retries**: Automatsko ponovno pokušavanje failed jobs
- **Monitoring**: Jasna visibility u `failed_jobs` tabeli
- **Scalability**: Može se dodati više workers po potrebi

---

## FAZA 3: Modernizacija

### FAZA 3.1: Uklanjanje AndroModel

**Status:** ✅ Završeno  
**Prioritet:** LOW  
**Rizik:** VISOK

#### Opis
57 modela trenutno nasleđuje `AndroModel` koji automatski instancira `null` relacije:

```php
// Trenutno ponašanje sa AndroModel
$kandidat = Kandidat::find(1);
$kandidat->studijskiProgram; // Vraća PRAZAN StudijskiProgram objekat čak i ako je NULL

// Željeno ponašanje sa vanilla Eloquent
$kandidat->studijskiProgram; // Vraća NULL ako relacija nije eager-loaded
```

#### Akcija
- Postepena migracija (po 5-10 modela odjednom)
- Zamena `extends AndroModel` sa `extends Model`
- Ažuriranje blade template-a sa null-safe operatorom (`?->`)
- Dodavanje explicit eager loading-a gde je potrebno

#### Benefit
- **Type safety**: Prisiljava eksplicitan null handling
- **Otkriva bugove**: Relacije koje nisu eager-loaded postaju vidljive
- **Standard Laravel**: Uklanja custom base model, lakše održavanje

#### Rizik
- Može otkriti stotine mesta gde se oslanja na auto-instantiation
- **Preporuka**: Raditi postepeno, testirati svaku grupu modela

---

### FAZA 3.2: Uvođenje DTOs (Data Transfer Objects)

**Status:** ✅ Završeno (delimično)  
**Prioritet:** LOW  
**Rizik:** NIZAK

#### Implementirano
- 10 DTO klasa u `app/DTOs/` (KandidatData, ZapisnikData, PrijavaIspitaData, itd.)
- `fromRequest()` pattern korišćen u KandidatController, IspitController

#### Opis
Trenutno service layer prima/vraća asocijativne nizove ili Request objekte direktno. DTOs bi uveli type safety i jasnu strukturu podataka.

```php
// Željeni pattern
$data = KandidatData::fromRequest($request);
$kandidat = $this->kandidatService->storeKandidat($data);
```

#### Benefit
- **Type safety**: IDE autocomplete i type checking
- **Dokumentacija**: DTO klasa jasno pokazuje šta metoda očekuje
- **Testiranje**: Lakše kreiranje test podataka

---

### FAZA 3.3: Dodavanje Policy Klasa

**Status:** ✅ Završeno  
**Prioritet:** LOW  
**Rizik:** NIZAK

#### Implementirano
- `KandidatPolicy`, `IspitPolicy`, `PrijavaIspitaPolicy`, `PolozeniIspitiPolicy`
- Sve registrovane u `AuthServiceProvider`
- `$this->authorize()` korišćen u `IspitController` i `PrijavaController`

#### Opis
Trenutno samo `KandidatPolicy` postoji za 57 modela. Authorization logika je rasuta po kontrolerima.

#### Akcija
- Kreirati Policy klase za IspitPolicy, PrijavaIspitaPolicy, PolozeniIspitiPolicy
- Registrovati u AuthServiceProvider
- Koristiti `$this->authorize()` u kontrolerima i `@can` u Blade template-ima

#### Benefit
- **Centralizovana autorizacija**: Sva logika na jednom mestu
- **Reusability**: Iste policy metode u kontroleru, blade, API
- **Testabilnost**: Lako testirati authorization pravila

---

## 🚧 Preostalo

### FAZA 3.4: Konsolidacija Frontend Stack-a

**Status:** Pending  
**Prioritet:** LOW  
**Procenjeno vreme:** 8-10 sati  
**Rizik:** SREDNJI (UI može privremeno biti broken)

#### Opis
Trenutno aplikacija koristi **mešavinu**:
- Bootstrap 5 + Tailwind CSS (konfliktni utility classes)
- jQuery + Alpine.js (dupla reaktivnost)

#### Akcija
- Konsolidovati na Tailwind + Alpine.js
- Ukloniti Bootstrap i jQuery zavisnosti
- Kreirati reusable Blade komponente (modal, dropdown, itd.)
- Raditi postepeno po modulima

#### Benefit
- **Manji bundle size**: ~40% manje JS/CSS
- **Konzistentnost**: Jedan utility-first CSS framework, jedan reaktivni library
- **Moderan stack**: Lakše održavanje

#### Rizik
- Zahteva ažuriranje svih view template-a (100+ fajlova)
- **Preporuka**: Raditi po modulima (npr. prvo kandidati, pa ispiti, pa obavestenja...)

---

### FAZA 3.5: Povećanje Test Coverage >70%

**Status:** ✅ Završeno  
**Prioritet:** LOW  
**Procenjeno vreme:** 10-12 sati  
**Rizik:** NIZAK

#### Rezultat
- **Pre**: 1378 testova, 3426 asercija, 63.03% line coverage
- **Posle**: 1519 testova, 3836 asercija, **71.51% line coverage** (4394/6145 linija)
- **Novi test fajlovi**:
  - `tests/Unit/Policies/IspitPolicyTest.php` — 21 test (IspitPolicy autorizacija)
  - `tests/Feature/IzvestajiControllerTest.php` — 19 testova (Excel/PDF izveštaji)
  - `tests/Feature/ChatbotControllerTest.php` — 12 testova (chatbot API endpoints)
  - `tests/Unit/Services/IspitPdfServiceTest.php` — 6 testova (PDF generacija)
  - `tests/Unit/Services/KandidatServiceExtendedTest.php` — 32 testa (storeKandidatPage1/2, storeMasterKandidat)
  - `tests/Unit/Services/PrijavaServiceTest.php` — 51 test (prijave ispita, diplomski CRUD, zapisnici)
- **Bug fix**: KandidatData DTO — `'JMBG'` → `'jmbg'` u `toArray()` metodi
- **Migracija**: Nullable kolone na 3 tabele (kandidat, predmet_program, godina_studija)
- **Ključna coverage poboljšanja**:
  - PrijavaService: 63.80% → 99.48% (+35.68%)
  - KandidatService: 90.71% → 99.17% (+8.46%)
  - IspitPolicy: 0% → 95.24%

#### Akcija

**1. Prioritizacija kritičnih flow-ova:**
- Enrollment flow (upis kandidata)
- Exam registration (prijava ispita)
- Grade submission (unos ocena)
- Zapisnik creation (kreiranje zapisnika)

**2. Kreiranje testova za nepokrivene module:**
- ChatbotController + ChatbotService testovi
- PredictionController + PredictionService testovi
- DashboardController testovi
- ObavestenjeController testovi

**3. Pokretanje sa coverage (koristiti pcov):**
```bash
php -d pcov.enabled=1 -d pcov.directory=app vendor/bin/phpunit --coverage-text
```

#### Target Coverage:
- **Services**: >80%
- **Controllers**: >60%
- **Overall lines**: >70%

#### Benefit
- **Confidence**: Siguran refactoring bez brige o breaking changes
- **Dokumentacija**: Testovi pokazuju kako sistem treba da funkcioniše
- **Regression prevention**: CI automatski hvata bugove

---

### FAZA 3.6: Scheduled Tasks (Backup, Monitoring, Cleanup)

**Status:** ✅ Završeno  
**Prioritet:** LOW  
**Procenjeno vreme:** 2-3 sata  
**Rizik:** NIZAK

#### Opis
Trenutno **nema automatizovanih taskova** za:
- Database backup
- Čišćenje starih notifikacija
- Arhiviranje završenih zapisnika
- Health monitoring

#### Akcija
- Kreirati Artisan command klase: BackupDatabase, CleanupOldNotifications, ArchiveCompletedZapisnici, HealthCheck
- Konfigurisati Laravel Scheduler u Console Kernel-u
- Aktivirati sa cron: `* * * * * cd /home/dragomix/fzs-laravel && php artisan schedule:run >> /dev/null 2>&1`

#### Benefit
- **Automatski backup**: Zaštita od gubitka podataka
- **Performance**: Čišćenje starih zapisa održava bazu brzom
- **Monitoring**: Health check alert-uje ako nešto ne radi

---

## Sledeći koraci

1. **Odmah**: Završiti FAZU 2.5 i 2.6 (Foreign Keys + Queue Jobs)
2. **Onda**: Krenuti sa FAZOM 3 po redosledu prioriteta

## Napomene

- Sve promene testirane sa PHPStan level 5, Pint i PHPUnit — nema novih grešaka
- Sve N+1 optimizacije verifikovane — koristiti `whereIn()->get()->keyBy('id')` pattern
- Storage facade pattern: `Storage::disk('uploads')->putFileAs(...)`
- Service layer pattern: thin controllers, business logic u service-ima
- Form Request pattern: validacija van kontrolera
- Named route pattern: `redirect()->route()` umesto hardcoded URL-ova
- Error logging pattern: `Log::error()` sa SQL/bindings kontekstom u catch blokovima
- Excel export pattern: Maatwebsite v4 sa `FromCollection` + `WithHeadings` interfejsima

## Resursi

- PHPStan dokumentacija: https://phpstan.org/
- Laravel Queue dokumentacija: https://laravel.com/docs/queues
- Laravel Policy dokumentacija: https://laravel.com/docs/authorization
- Tailwind CSS: https://tailwindcss.com/
- Alpine.js: https://alpinejs.dev/
- Maatwebsite Excel: https://docs.laravel-excel.com/

---

**Verzija:** 1.2  
**Datum:** 2026-04-15  
**Autor:** Senior Architect Audit + AI Assistant
