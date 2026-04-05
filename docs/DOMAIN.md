# Domain Glossary

Complete business domain reference for FZS Laravel application.

## Core Entities

### Kandidat (Student Applicant)
Student candidate applying to the faculty. Contains personal info, high school grades, sports engagement, and submitted documents.

**Key fields:**
- imeKandidata, prezimeKandidata, jmbg
- statusUpisa_id (Status: Prijavljen, Upisan, Odbijen)
- studijskiProgram_id, tipStudija_id, godinaStudija_id
- brojBodovaTest, brojBodovaSkola, ukupniBrojBodova

**Related entities:** UspehSrednjaSkola, SportskoAngazovanje, KandidatPrilozenaDokumenta

---

### Ispit (Exam)
Entrance exam or course exam.

**Types:**
- Prijemni ispit (entrance exam)
- Course exams by subject

**Key fields:**
- nazivIspita
- ispitniRok_id (exam period)
- datumOdrzavanja

---

### Upis (Enrollment)
Student enrollment process by academic year.

**Key entities:**
- UpisGodine: Enrollment by year (1st, 2nd, 3rd, 4th year)
- SkolskaGodUpisa: Academic year (2023/2024, etc.)

---

### Studijski Program (Study Program)
Academic programs offered by the faculty.

**Types (tipStudija_id):**
1. Osnovne studije (Bachelor)
2. Master studije (Master)
3. Doktorske studije (PhD)

---

### UspehSrednjaSkola (High School Grades)
High school performance for 4 years.

**Fields:**
- RedniBrojRazreda (1-4)
- opstiUspeh_id (overall grade: Odličan, Vrlo dobar, Dobar)
- srednja_ocena (average grade)

---

### SportskoAngazovanje (Sports Engagement)
Student's sports activities (extra points for enrollment).

**Fields:**
- sport_id
- nazivKluba (club name)
- ukupnoGodina (total years of engagement)

---

### PrilozenaDokumenta (Submitted Documents)
Documents submitted by candidates during application.

**Types:**
- skolskaGodina_id = 1: Prva godina (diploma, birth certificate, etc.)
- skolskaGodina_id = 2: Ostale godine (additional documents)

---

### StatusUpisa (Enrollment Status)
Status of candidate application.

**Statuses:**
1. Prijavljen (Applied)
2. U obradi (In progress)
3. Upisan (Enrolled)
4. Odbijen (Rejected)

---

### Ispitни Rok (Exam Period)
Exam session periods.

**Examples:**
- Junski rok (June session)
- Septembrski rok (September session)
- Januarski rok (January session)

---

## Workflows

### 1. Candidate Application (2-step process)

**Step 1: Basic Information**
- Personal data (name, JMBG, address, phone)
- Study program selection
- Photo upload

**Step 2: Grades & Documents**
- High school grades (4 years)
- Sports engagement (optional)
- Document submission
- Scoring calculation

**Controller:** KandidatController::store()
**Service:** KandidatService::storeKandidatPage1(), ::storeKandidatPage2()

---

### 2. Scoring & Ranking

**Bodovanje (Scoring):**
- brojBodovaTest: Entrance exam score
- brojBodovaSkola: High school score
- brojBodovaPort: Sports score (optional)
- **ukupniBrojBodova** = Test + School + Sports

**Rangiranje (Ranking):**
Candidates ranked by ukupniBrojBodova for enrollment decisions.

---

### 3. Enrollment

Admin reviews applications and updates statusUpisa_id:
- Prijavljen → Upisan (accepted)
- Prijavljen → Odbijen (rejected)

Enrolled students get brojIndeksa (student index number).

---

## Technical Terms

- **indikatorAktivan**: Soft delete flag (1 = active, 0 = inactive)
- **DB transactions**: Used in KandidatService, IspitService for data consistency
- **Cache**: Active studijski program cached for 1 hour
- **Storage disk 'uploads'**: File storage for images/PDFs (images/, pdf/ directories)

---

## Common Relationships

```
Kandidat
├── belongsTo: StudijskiProgram
├── belongsTo: TipStudija
├── belongsTo: GodinaStudija
├── belongsTo: StatusStudiranja (statusUpisa)
├── hasMany: UspehSrednjaSkola (4 records — one per razred)
├── hasMany: SportskoAngazovanje
└── hasMany: KandidatPrilozenaDokumenta

Ispit
├── belongsTo: IspitniRok
├── belongsTo: Predmet
└── hasMany: PrijavaIspita

PrijavaIspita
├── belongsTo: Ispit
└── belongsTo: Kandidat
```
