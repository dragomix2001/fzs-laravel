# FZS-Laravel: Preostala Poboljšanja

Ovaj dokument opisuje preostale arhitektonske i funkcionalnosti poboljšanja za FZS-Laravel aplikaciju nakon uspešno završenih Faza 1 i 2.

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

### Faza 2 — Arhitektonski Refaktoring (4/6 završeno)
- [x] **2.1** Ekstrahovana business logika KandidatController → KandidatService
- [x] **2.2** Ekstrahovana business logika IspitController → IspitService
- [x] **2.3** Ekstrahovane static metode UpisGodine → UpisService
- [x] **2.4** Standardizovane validacije na Form Request klase

---

## 🚧 Preostalo

### FAZA 2.5: Dodavanje Foreign Key Constraints

**Status:** Pending  
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
        // Ako postoje NULL vrednosti, prvo ih popuniti sa default vrednostima
        $table->foreign('studijskiProgram_id')
              ->references('id')->on('studijski_program')
              ->onDelete('restrict'); // Ne dozvoliti brisanje programa koji ima kandidate
        
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
              ->onDelete('cascade'); // Ako se kandidat obriše, obrisati i sve upise
        
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
              ->onDelete('set null'); // Dozvoli null ako se zapisnik obriše
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

    // Dodati ostale tabele po potrebi...
}

public function down()
{
    // Dropovanje constraints u obrnutom redosledu
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
// Ako postoje orphaned zapisi, kreirati data cleanup skriptu
php artisan make:command CleanupOrphanedRecords

// app/Console/Commands/CleanupOrphanedRecords.php
public function handle()
{
    // Obrisati ili popraviti orphaned zapise
    $this->info('Cleaning up orphaned records...');
    
    // Primer: postaviti default vrednost za NULL foreign keys
    DB::table('kandidat')
        ->whereNull('studijskiProgram_id')
        ->update(['studijskiProgram_id' => 1]); // ID default programa
    
    $this->info('Cleanup complete!');
}
```

**5. Testiranje:**
```bash
# Backup baze pre pokretanja
mysqldump -u root -p fzs > backup_before_fk_constraints.sql

# Pokrenuti migraciju
php artisan migrate

# Testirati da li constrainti rade
# Pokušati obrisati kandidata koji ima upise godine (treba da odbije)
# Pokušati kreirati prijavu ispita sa nepostojećim kandidatom (treba da odbije)
```

#### Benefit
- **Integritet podataka**: Spreči orphaned zapise
- **Sigurnost**: Automatsko čišćenje kaskadnim brisanjem gde je potrebno
- **Dokumentacija**: Foreign keys jasno pokazuju relacije u bazi

---

### FAZA 2.6: Kreiranje Queued Job Klasa

**Status:** Pending  
**Prioritet:** MEDIUM  
**Procenjeno vreme:** 2-3 sata  
**Rizik:** NIZAK (ne utiče na postojeću funkcionalnost)

#### Opis
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
namespace App\Jobs;

use App\Obavestenje;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

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
        // Logika iz NotificationService::broadcastObavestenje()
        // Slanje notifikacija user-by-user ili batch
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

**3. Implementacija `GenerateZapisnikPdfJob`:**

```php
// app/Jobs/GenerateZapisnikPdfJob.php
namespace App\Jobs;

use App\Services\IspitService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateZapisnikPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 300; // PDF generation može biti sporo

    public function __construct(
        public int $zapisnikId,
        public string $storagePath
    ) {}

    public function handle(IspitService $ispitService)
    {
        $request = new \Illuminate\Http\Request(['id' => $this->zapisnikId]);
        
        // Generisati PDF i sačuvati na disk umesto slanja kao response
        $pdf = $ispitService->zapisnikStampa($request);
        
        \Storage::disk('local')->put($this->storagePath, $pdf->output());
    }
}
```

**4. Implementacija `MassEnrollmentJob`:**

```php
// app/Jobs/MassEnrollmentJob.php
namespace App\Jobs;

use App\Services\UpisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MassEnrollmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1; // Ne želimo duplikate upisa
    public $timeout = 600; // 10 minuta za velike batch-eve

    public function __construct(
        public array $kandidatIds
    ) {}

    public function handle(UpisService $upisService)
    {
        foreach ($this->kandidatIds as $kandidatId) {
            try {
                $upisService->registrujKandidata($kandidatId);
            } catch (\Exception $e) {
                \Log::error("Mass enrollment failed for kandidat {$kandidatId}", [
                    'error' => $e->getMessage()
                ]);
                // Nastavi sa ostalima
            }
        }
    }
}
```

**5. Ažuriranje Service/Controller metoda:**

```php
// NotificationService
public function broadcastObavestenje(Obavestenje $obavestenje, array $userIds)
{
    // Umesto direktnog slanja, dispatch job
    BroadcastNotificationJob::dispatch($obavestenje, $userIds);
}

// KandidatService
public function masovniUpis(array $kandidatIds)
{
    // Umesto for petlje, dispatch job
    MassEnrollmentJob::dispatch($kandidatIds);
    
    return ['status' => 'queued', 'count' => count($kandidatIds)];
}

// IspitController
public function generatePdf(Request $request)
{
    $storagePath = 'pdfs/zapisnik_' . $request->id . '_' . time() . '.pdf';
    
    GenerateZapisnikPdfJob::dispatch($request->id, $storagePath);
    
    return response()->json([
        'status' => 'generating',
        'download_url' => route('zapisnik.download', ['path' => $storagePath])
    ]);
}
```

**6. Konfiguracija Queue:**

```bash
# .env je već konfigurisan sa QUEUE_CONNECTION=redis
# Pokrenuti queue worker
php artisan queue:work redis --tries=3 --timeout=300

# Za production, koristiti Supervisor
```

**7. Supervisor konfiguracija (za production):**

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

## FAZA 3: Modernizacija (LOW Priority)

### FAZA 3.1: Uklanjanje AndroModel

**Status:** Pending  
**Prioritet:** LOW  
**Procenjeno vreme:** 6-8 sati  
**Rizik:** VISOK (može otkriti skrivene bugove)

#### Opis
57 modela trenutno nasleđuje `AndroModel` koji automatski instancira `null` relacije:

```php
// Trenutno ponašanje sa AndroModel
$kandidat = Kandidat::find(1);
$kandidat->studijskiProgram; // Vraća PRAZAN StudijskiProgram objekat čak i ako je NULL
// Ovo skriva bugove gde relacija nije učitana!

// Željeno ponašanje sa vanilla Eloquent
$kandidat->studijskiProgram; // Vraća NULL ako relacija nije eager-loaded
```

#### Akcija

**1. Analiza impact:**
```bash
# Naći sve klase koje extend AndroModel
grep -r "extends AndroModel" app/

# Naći sve pristupe relacijama koji mogu biti NULL
grep -r '\$[a-zA-Z_]*->[a-zA-Z_]*->' app/ | grep -v '?->'
```

**2. Postepena migracija (po 5-10 modela odjednom):**

```php
// Primer: Kandidat model
// STARO:
class Kandidat extends AndroModel
{
    public function studijskiProgram()
    {
        return $this->belongsTo(StudijskiProgram::class);
    }
}

// NOVO:
class Kandidat extends Model
{
    use Auditable; // Već koristi ovaj trait
    
    public function studijskiProgram(): ?StudijskiProgram
    {
        return $this->belongsTo(StudijskiProgram::class);
    }
}
```

**3. Ažuriranje blade template-a:**

```blade
{{-- STARO (auto-instancira prazan objekat) --}}
{{ $kandidat->studijskiProgram->naziv }}

{{-- NOVO (mora biti null-safe) --}}
{{ $kandidat->studijskiProgram?->naziv ?? 'N/A' }}
```

**4. Ažuriranje kontrolera sa eager loading:**

```php
// STARO (oslanja se na auto-instantiation)
$kandidati = Kandidat::all();
foreach ($kandidati as $kandidat) {
    echo $kandidat->studijskiProgram->naziv; // Može biti prazan objekat!
}

// NOVO (eksplicitno eager-load)
$kandidati = Kandidat::with('studijskiProgram')->get();
foreach ($kandidati as $kandidat) {
    echo $kandidat->studijskiProgram?->naziv ?? 'Nema program';
}
```

**5. Testing:**
```bash
# Pokrenuti sve testove nakon svake grupe modela
php artisan test

# Proveriti u browser-u kritične stranice:
# - Lista kandidata
# - Detalji kandidata
# - Upis godine
# - Prijava ispita
```

#### Benefit
- **Type safety**: Prisiljava eksplicitan null handling
- **Otkriva bugove**: Relacije koje nisu eager-loaded postaju vidljive
- **Standard Laravel**: Uklanja custom base model, lakše održavanje

#### Rizik
- Može otkriti stotine mesta gde se oslanja na auto-instantiation
- Zahteva ažuriranje view template-a i kontrolera
- **Preporuka**: Raditi postepeno, testirati svaku grupu modela

---

### FAZA 3.2: Uvođenje DTOs (Data Transfer Objects)

**Status:** Pending  
**Prioritet:** LOW  
**Procenjeno vreme:** 4-5 sati  
**Rizik:** NIZAK

#### Opis
Trenutno service layer prima/vraća asocijativne nizove:

```php
// Trenutno
$kandidatService->storeKandidat($request); // $request je Request objekat
// Servis interno pristupa $request->input('ime'), $request->input('prezime')...

// Problem: Nema type hinting, autocomplete, validacije strukture
```

#### Akcija

**1. Kreirati DTO klase:**

```bash
php artisan make:class DTOs/KandidatData
php artisan make:class DTOs/ZapisnikData
php artisan make:class DTOs/PrijavaIspitaData
```

**2. Implementacija DTO pattern-a:**

```php
// app/DTOs/KandidatData.php
namespace App\DTOs;

class KandidatData
{
    public function __construct(
        public string $ime,
        public string $prezime,
        public string $JMBG,
        public int $studijskiProgramId,
        public int $tipStudijaId,
        public ?string $brojIndeksa = null,
        public ?int $godinaStudijaId = null,
    ) {}

    public static function fromRequest(\Illuminate\Http\Request $request): self
    {
        return new self(
            ime: $request->input('ImeKandidata'),
            prezime: $request->input('PrezimeKandidata'),
            JMBG: $request->input('JMBG'),
            studijskiProgramId: (int) $request->input('StudijskiProgram'),
            tipStudijaId: (int) $request->input('TipStudija'),
            brojIndeksa: $request->input('BrojIndeksa'),
            godinaStudijaId: $request->filled('GodinaStudija') ? (int) $request->input('GodinaStudija') : null,
        );
    }

    public function toArray(): array
    {
        return [
            'imeKandidata' => $this->ime,
            'prezimeKandidata' => $this->prezime,
            'JMBG' => $this->JMBG,
            'studijskiProgram_id' => $this->studijskiProgramId,
            'tipStudija_id' => $this->tipStudijaId,
            'brojIndeksa' => $this->brojIndeksa,
            'godinaStudija_id' => $this->godinaStudijaId,
        ];
    }
}
```

**3. Ažuriranje Service metoda:**

```php
// KandidatService
use App\DTOs\KandidatData;

public function storeKandidat(KandidatData $data): Kandidat
{
    $kandidat = Kandidat::create($data->toArray());
    
    // Sad imamo type safety i autocomplete!
    if ($data->godinaStudijaId !== null) {
        // ...
    }
    
    return $kandidat;
}
```

**4. Ažuriranje Controller-a:**

```php
// KandidatController
public function store(StoreKandidatRequest $request)
{
    $data = KandidatData::fromRequest($request);
    $kandidat = $this->kandidatService->storeKandidat($data);
    
    // ...
}
```

#### Benefit
- **Type safety**: IDE autocomplete i type checking
- **Validacija**: Struktura podataka eksplicitna
- **Dokumentacija**: DTO klasa jasno pokazuje šta metoda očekuje
- **Testiranje**: Lakše kreiranje test podataka

---

### FAZA 3.3: Dodavanje Policy Klasa

**Status:** Pending  
**Prioritet:** LOW  
**Procenjeno vreme:** 3-4 sata  
**Rizik:** NIZAK

#### Opis
Trenutno samo `KandidatPolicy` postoji za 57 modela. Authorization logika je rasuta po kontrolerima.

#### Akcija

**1. Kreirati Policy klase:**

```bash
php artisan make:policy IspitPolicy --model=ZapisnikOPolaganjuIspita
php artisan make:policy PrijavaIspitaPolicy --model=PrijavaIspita
php artisan make:policy PolozeniIspitiPolicy --model=PolozeniIspiti
```

**2. Implementacija Policy:**

```php
// app/Policies/IspitPolicy.php
namespace App\Policies;

use App\Models\User;
use App\ZapisnikOPolaganjuIspita;

class IspitPolicy
{
    public function viewAny(User $user): bool
    {
        // Samo admin i profesori mogu videti sve zapiske
        return $user->hasRole(['admin', 'profesor']);
    }

    public function view(User $user, ZapisnikOPolaganjuIspita $zapisnik): bool
    {
        // Admin može sve, profesor samo svoje
        if ($user->hasRole('admin')) {
            return true;
        }
        
        return $user->profesor_id === $zapisnik->profesor_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'profesor']);
    }

    public function update(User $user, ZapisnikOPolaganjuIspita $zapisnik): bool
    {
        // Samo kreator može ažurirati
        return $user->profesor_id === $zapisnik->profesor_id || $user->hasRole('admin');
    }

    public function delete(User $user, ZapisnikOPolaganjuIspita $zapisnik): bool
    {
        // Samo admin može brisati
        return $user->hasRole('admin');
    }

    public function arhiviraj(User $user, ZapisnikOPolaganjuIspita $zapisnik): bool
    {
        // Samo kreator ili admin može arhivirati
        return $user->profesor_id === $zapisnik->profesor_id || $user->hasRole('admin');
    }
}
```

**3. Registracija u AuthServiceProvider:**

```php
// app/Providers/AuthServiceProvider.php
protected $policies = [
    Kandidat::class => KandidatPolicy::class,
    ZapisnikOPolaganjuIspita::class => IspitPolicy::class,
    PrijavaIspita::class => PrijavaIspitaPolicy::class,
    PolozeniIspiti::class => PolozeniIspitiPolicy::class,
];
```

**4. Korišćenje u kontrolerima:**

```php
// IspitController
public function storeZapisnik(StoreZapisnikRequest $request)
{
    $this->authorize('create', ZapisnikOPolaganjuIspita::class);
    
    // ...
}

public function updateZapisnik(Request $request, $id)
{
    $zapisnik = ZapisnikOPolaganjuIspita::findOrFail($id);
    $this->authorize('update', $zapisnik);
    
    // ...
}
```

**5. Korišćenje u Blade:**

```blade
@can('create', App\ZapisnikOPolaganjuIspita::class)
    <a href="{{ route('zapisnik.create') }}">Kreiraj novi zapisnik</a>
@endcan

@can('update', $zapisnik)
    <a href="{{ route('zapisnik.edit', $zapisnik->id) }}">Izmeni</a>
@endcan
```

#### Benefit
- **Centralizovana autorizacija**: Sva logika na jednom mestu
- **Reusability**: Iste policy metode mogu se koristiti u kontroleru, blade, API
- **Testabilnost**: Lako testirati authorization pravila

---

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

**1. Analiza trenutne upotrebe:**

```bash
# jQuery usage
grep -r "jQuery\|\\$(" resources/views/ | wc -l

# Alpine.js usage
grep -r "x-data\|@click" resources/views/ | wc -l

# Bootstrap komponente
grep -r "class=\".*btn-primary\|modal\|dropdown" resources/views/

# Tailwind classes
grep -r "class=\".*flex\|grid\|text-" resources/views/
```

**2. Odluka: Tailwind + Alpine.js (moderni stack)**

**3. Uklanjanje Bootstrap:**

```bash
# Ukloniti iz package.json
npm uninstall bootstrap @popperjs/core

# Ukloniti Bootstrap import iz app.css
# resources/css/app.css - obrisati liniju:
# @import 'bootstrap';
```

**4. Refaktorisanje komponenti (Blade template-i):**

```blade
{{-- STARO (Bootstrap) --}}
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal">
    Otvori
</button>

<div class="modal fade" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Naslov</h5>
            </div>
            <div class="modal-body">
                Sadržaj
            </div>
        </div>
    </div>
</div>

{{-- NOVO (Tailwind + Alpine.js) --}}
<div x-data="{ open: false }">
    <button @click="open = true" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Otvori
    </button>

    <div x-show="open" 
         x-cloak
         @click.away="open = false"
         class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b">
                <h5 class="text-lg font-semibold">Naslov</h5>
            </div>
            <div class="px-6 py-4">
                Sadržaj
            </div>
        </div>
    </div>
</div>
```

**5. Uklanjanje jQuery:**

```bash
# Ukloniti iz package.json
npm uninstall jquery

# Refaktorisati jQuery kod u vanilla JS ili Alpine.js
```

```javascript
// STARO (jQuery)
$(document).ready(function() {
    $('#submitBtn').on('click', function() {
        $.ajax({
            url: '/api/endpoint',
            method: 'POST',
            data: { foo: 'bar' },
            success: function(response) {
                alert('Uspeh!');
            }
        });
    });
});

// NOVO (Alpine.js)
<div x-data="{ 
    loading: false,
    async submit() {
        this.loading = true;
        try {
            const response = await fetch('/api/endpoint', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ foo: 'bar' })
            });
            const data = await response.json();
            alert('Uspeh!');
        } catch (error) {
            console.error(error);
        } finally {
            this.loading = false;
        }
    }
}">
    <button @click="submit()" :disabled="loading">
        <span x-show="!loading">Pošalji</span>
        <span x-show="loading">Učitavanje...</span>
    </button>
</div>
```

**6. Kreiranje reusable komponenti:**

```blade
{{-- resources/views/components/modal.blade.php --}}
@props(['title'])

<div x-data="{ open: false }" x-cloak>
    <div @click="open = true">
        {{ $trigger }}
    </div>

    <div x-show="open" 
         @click.away="open = false"
         class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h5 class="text-lg font-semibold">{{ $title }}</h5>
                <button @click="open = false">&times;</button>
            </div>
            <div class="px-6 py-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>

{{-- Upotreba --}}
<x-modal title="Potvrda">
    <x-slot name="trigger">
        <button class="btn-primary">Otvori modal</button>
    </x-slot>
    
    Da li ste sigurni?
</x-modal>
```

**7. Build i test:**

```bash
npm install
npm run build

# Testirati svaku stranicu u browser-u
# Posebno paziti na:
# - Modale
# - Dropdowns
# - Form validacije
# - AJAX zahtevi
```

#### Benefit
- **Manji bundle size**: ~40% manje JS/CSS
- **Moderan stack**: Lakše održavanje, bolji developer experience
- **Konzistentnost**: Jedan utility-first CSS framework, jedan reaktivni library

#### Rizik
- Zahteva ažuriranje svih view template-a (100+ fajlova)
- Privremeno može broken UI tokom refactoringa
- **Preporuka**: Raditi po modulima (npr. prvo kandidati, pa ispiti, pa obavestenja...)

---

### FAZA 3.5: Povećanje Test Coverage >60%

**Status:** Pending  
**Prioritet:** LOW  
**Procenjeno vreme:** 10-12 sati  
**Rizik:** NIZAK

#### Opis
Trenutno:
- ~15 feature testova
- ~12% code coverage
- Kritični flowovi nisu pokriveni

#### Akcija

**1. Prioritizacija kritičnih flow-ova:**

- **Enrollment flow** (upis kandidata)
- **Exam registration** (prijava ispita)
- **Grade submission** (unos ocena)
- **Zapisnik creation** (kreiranje zapisnika)

**2. Feature test primer — Enrollment:**

```php
// tests/Feature/EnrollmentTest.php
namespace Tests\Feature;

use Tests\TestCase;
use App\Kandidat;
use App\StudijskiProgram;
use App\TipStudija;
use App\Services\UpisService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed basic reference data
        $this->artisan('db:seed', ['--class' => 'ReferenceDataSeeder']);
    }

    /** @test */
    public function it_enrolls_kandidat_for_osnovne_studije()
    {
        $kandidat = Kandidat::factory()->create([
            'tipStudija_id' => 1, // Osnovne
            'godinaStudija_id' => 1,
        ]);

        $upisService = app(UpisService::class);
        $upisService->registrujKandidata($kandidat->id);

        // Assert 4 upis_godine records created (godine 1-4)
        $this->assertDatabaseCount('upis_godine', 4);
        
        $this->assertDatabaseHas('upis_godine', [
            'kandidat_id' => $kandidat->id,
            'godina' => 1,
            'statusGodine_id' => 1, // Upisan
        ]);
        
        $this->assertDatabaseHas('upis_godine', [
            'kandidat_id' => $kandidat->id,
            'godina' => 2,
            'statusGodine_id' => 3, // Nije upisan još
        ]);
    }

    /** @test */
    public function it_generates_unique_broj_indeksa()
    {
        $kandidat = Kandidat::factory()->create([
            'brojIndeksa' => null,
        ]);

        $upisService = app(UpisService::class);
        $upisService->generisiBrojIndeksa($kandidat->id);

        $kandidat->refresh();
        
        $this->assertNotNull($kandidat->brojIndeksa);
        $this->assertMatchesRegularExpression('/^\d{4}\/\d{4}$/', $kandidat->brojIndeksa);
    }

    /** @test */
    public function it_prevents_duplicate_enrollment()
    {
        $kandidat = Kandidat::factory()->create([
            'tipStudija_id' => 1,
        ]);

        $upisService = app(UpisService::class);
        
        // First enrollment
        $upisService->registrujKandidata($kandidat->id);
        $this->assertDatabaseCount('upis_godine', 4);
        
        // Second enrollment should be ignored
        $upisService->registrujKandidata($kandidat->id);
        $this->assertDatabaseCount('upis_godine', 4); // Still 4, not 8
    }
}
```

**3. Feature test primer — Exam Registration:**

```php
// tests/Feature/ExamRegistrationTest.php
namespace Tests\Feature;

use Tests\TestCase;
use App\Kandidat;
use App\Predmet;
use App\PredmetProgram;
use App\Profesor;
use App\AktivniIspitniRokovi;
use App\PrijavaIspita;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExamRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function kandidat_can_register_for_exam()
    {
        $kandidat = Kandidat::factory()->create();
        $predmet = Predmet::factory()->create();
        $profesor = Profesor::factory()->create();
        $rok = AktivniIspitniRokovi::factory()->create();
        
        PredmetProgram::factory()->create([
            'predmet_id' => $predmet->id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
        ]);

        $response = $this->actingAs($kandidat->user)
            ->post(route('prijava.store'), [
                'predmet_id' => $predmet->id,
                'profesor_id' => $profesor->id,
                'rok_id' => $rok->id,
                'kandidat_id' => $kandidat->id,
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('prijava_ispita', [
            'kandidat_id' => $kandidat->id,
            'predmet_id' => $predmet->id,
            'rok_id' => $rok->id,
        ]);
    }

    /** @test */
    public function it_prevents_duplicate_exam_registration()
    {
        $kandidat = Kandidat::factory()->create();
        $predmet = Predmet::factory()->create();
        $profesor = Profesor::factory()->create();
        $rok = AktivniIspitniRokovi::factory()->create();

        PrijavaIspita::factory()->create([
            'kandidat_id' => $kandidat->id,
            'predmet_id' => $predmet->id,
            'rok_id' => $rok->id,
        ]);

        // Try to register again
        $response = $this->actingAs($kandidat->user)
            ->post(route('prijava.store'), [
                'predmet_id' => $predmet->id,
                'profesor_id' => $profesor->id,
                'rok_id' => $rok->id,
                'kandidat_id' => $kandidat->id,
            ]);

        // Should fail with validation error
        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('prijava_ispita', 1); // Still only 1
    }
}
```

**4. Kreiranje Factory klasa:**

```bash
php artisan make:factory KandidatFactory
php artisan make:factory PredmetFactory
php artisan make:factory ProfesorFactory
# itd...
```

```php
// database/factories/KandidatFactory.php
namespace Database\Factories;

use App\Kandidat;
use App\StudijskiProgram;
use App\TipStudija;
use Illuminate\Database\Eloquent\Factories\Factory;

class KandidatFactory extends Factory
{
    protected $model = Kandidat::class;

    public function definition()
    {
        return [
            'imeKandidata' => $this->faker->firstName(),
            'prezimeKandidata' => $this->faker->lastName(),
            'JMBG' => $this->faker->unique()->numerify('#############'),
            'studijskiProgram_id' => StudijskiProgram::factory(),
            'tipStudija_id' => TipStudija::factory(),
            'godinaStudija_id' => 1,
            'statusUpisa_id' => 1,
        ];
    }
}
```

**5. Pokretanje testova sa coverage:**

```bash
# Instalirati xdebug ako nije
php artisan test --coverage

# Ili detaljniji HTML report
php artisan test --coverage-html=coverage
```

#### Target Coverage po modulu:
- **Enrollment**: >80% (kritičan flow)
- **Exam Registration**: >80%
- **Zapisnik**: >70%
- **Grades**: >70%
- **Controllers**: >50% (većina je delegacija)
- **Services**: >80%
- **Overall**: >60%

#### Benefit
- **Confidence**: Siguran refactoring bez brige o breaking changes
- **Dokumentacija**: Testovi pokazuju kako sistem treba da funkcioniše
- **Regression prevention**: CI automatski hvata bugove

---

### FAZA 3.6: Scheduled Tasks (Backup, Monitoring, Cleanup)

**Status:** Pending  
**Prioritet:** LOW  
**Procenjeno vreme:** 2-3 sata  
**Rizik:** NIZAK

#### Opis
Trenutno **nema automatizovanih taskova** za:
- Database backup
- Čišćenje starih notifikacija
- Arhiviranje završenih zapisnika
- Monitoring

#### Akcija

**1. Konfiguracija Scheduler-a:**

```php
// app/Console/Kernel.php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Daily database backup at 2 AM
        $schedule->command('backup:run')
            ->dailyAt('02:00')
            ->onSuccess(function () {
                \Log::info('Database backup completed successfully');
            })
            ->onFailure(function () {
                \Log::error('Database backup failed!');
                // TODO: Send notification to admin
            });

        // Weekly cleanup of old notifications (older than 90 days)
        $schedule->command('notifications:cleanup')
            ->weekly()
            ->sundays()
            ->at('03:00');

        // Monthly archive of completed zapisnici
        $schedule->command('zapisnici:archive')
            ->monthlyOn(1, '04:00');

        // Daily health check
        $schedule->command('health:check')
            ->everyFiveMinutes();
    }
}
```

**2. Kreiranje Command klasa:**

```bash
php artisan make:command BackupDatabase
php artisan make:command CleanupOldNotifications
php artisan make:command ArchiveCompletedZapisnici
php artisan make:command HealthCheck
```

**3. Implementacija Backup Command:**

```php
// app/Console/Commands/BackupDatabase.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'backup:run {--compress}';
    protected $description = 'Backup MySQL database';

    public function handle()
    {
        $this->info('Starting database backup...');

        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = storage_path('app/backups/' . $filename);

        // Ensure directory exists
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        // mysqldump command
        $command = sprintf(
            'mysqldump -u%s -p%s %s > %s',
            env('DB_USERNAME'),
            env('DB_PASSWORD'),
            env('DB_DATABASE'),
            $filepath
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $this->error('Backup failed!');
            return 1;
        }

        // Optional: Compress
        if ($this->option('compress')) {
            exec("gzip {$filepath}");
            $filepath .= '.gz';
        }

        $this->info("Backup saved to: {$filepath}");

        // Delete backups older than 30 days
        $this->cleanupOldBackups();

        return 0;
    }

    protected function cleanupOldBackups()
    {
        $backupDir = storage_path('app/backups');
        $files = glob($backupDir . '/backup_*.sql*');
        $thirtyDaysAgo = strtotime('-30 days');

        foreach ($files as $file) {
            if (filemtime($file) < $thirtyDaysAgo) {
                unlink($file);
                $this->line("Deleted old backup: " . basename($file));
            }
        }
    }
}
```

**4. Implementacija Cleanup Notifications:**

```php
// app/Console/Commands/CleanupOldNotifications.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Obavestenje;
use Carbon\Carbon;

class CleanupOldNotifications extends Command
{
    protected $signature = 'notifications:cleanup {--days=90}';
    protected $description = 'Delete notifications older than specified days';

    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $count = Obavestenje::where('created_at', '<', $cutoffDate)
            ->where('vazno', false) // Keep important notifications
            ->delete();

        $this->info("Deleted {$count} old notifications.");

        return 0;
    }
}
```

**5. Implementacija Archive Zapisnici:**

```php
// app/Console/Commands/ArchiveCompletedZapisnici.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\ZapisnikOPolaganjuIspita;

class ArchiveCompletedZapisnici extends Command
{
    protected $signature = 'zapisnici:archive';
    protected $description = 'Archive zapisnici from previous school year';

    public function handle()
    {
        // Logic to determine which zapisnici should be archived
        // Based on rok_id and school year
        
        $archived = ZapisnikOPolaganjuIspita::where('arhiviran', false)
            ->whereHas('rok', function ($query) {
                // Rokovi from previous school year
                $query->where('skolskaGodina_id', '<', currentSchoolYear());
            })
            ->update(['arhiviran' => true]);

        $this->info("Archived {$archived} zapisnici.");

        return 0;
    }
}
```

**6. Implementacija Health Check:**

```php
// app/Console/Commands/HealthCheck.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class HealthCheck extends Command
{
    protected $signature = 'health:check';
    protected $description = 'Check application health';

    public function handle()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
        ];

        foreach ($checks as $name => $status) {
            if ($status) {
                $this->info("✓ {$name}: OK");
            } else {
                $this->error("✗ {$name}: FAILED");
                \Log::error("Health check failed: {$name}");
            }
        }

        $allHealthy = !in_array(false, $checks);
        return $allHealthy ? 0 : 1;
    }

    protected function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function checkRedis(): bool
    {
        try {
            Redis::ping();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function checkStorage(): bool
    {
        try {
            Storage::disk('local')->exists('test.txt');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function checkQueue(): bool
    {
        try {
            // Check if queue worker is running
            $output = shell_exec('ps aux | grep "queue:work" | grep -v grep');
            return !empty($output);
        } catch (\Exception $e) {
            return false;
        }
    }
}
```

**7. Aktiviranje Scheduler-a (cron):**

```bash
# Dodati u crontab
crontab -e

# Dodati red:
* * * * * cd /home/dragomix/fzs-laravel && php artisan schedule:run >> /dev/null 2>&1
```

**8. Testiranje:**

```bash
# Testirati svaki command ručno
php artisan backup:run
php artisan notifications:cleanup --days=30
php artisan zapisnici:archive
php artisan health:check

# Testirati scheduler
php artisan schedule:run
```

#### Benefit
- **Automatski backup**: Zaštita od gubitka podataka
- **Performance**: Čišćenje starih zapisa održava bazu brzom
- **Monitoring**: Health check alert-uje ako nešto ne radi
- **Maintenance-free**: Sve se dešava automatski

---

## Sledeći koraci

1. **Odmah**: Završiti FAZU 2.5 i 2.6 (Foreign Keys + Queue Jobs)
2. **Onda**: Krenuti sa FAZOM 3 po redosledu prioriteta

## Napomene

- Sve promene testirane sa `lsp_diagnostics` — nema novih gre šaka
- Sve N+1 optimizacije verifikovane — koristiti `whereIn()->get()->keyBy('id')` pattern
- Storage facade pattern: `Storage::disk('uploads')->putFileAs(...)`
- Service layer pattern: thin controllers, business logic u service-ima
- Form Request pattern: validacija van kontrolera

## Resursi

- PHPStan dokumentacija: https://phpstan.org/
- Laravel Queue dokumentacija: https://laravel.com/docs/queues
- Laravel Policy dokumentacija: https://laravel.com/docs/authorization
- Tailwind CSS: https://tailwindcss.com/
- Alpine.js: https://alpinejs.dev/

---

**Verzija:** 1.0  
**Datum:** 2026-04-01  
**Autor:** Senior Architect Audit + AI Assistant
