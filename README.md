# CapstoneLens

CapstoneLens is a Laravel prototype for reviewing capstone proposals against a local academic manuscript repository.

## What It Includes

- Local PDF manuscript repository and continuous-scroll reader.
- PDF and DOCX proposal upload with progress feedback.
- Local, CPU-friendly similarity analysis with supporting passage and page references.
- Similarity categories for topical and methodological overlap.

## Requirements

- PHP 8.3 or later and Composer
- Node.js and npm
- Python 3.14
- Poppler (`pdftoppm` and `pdfinfo`) for PDF rendering

## Setup

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File -Path database/database.sqlite -Force
php artisan migrate
npm install
npm run build
python -m pip install -r requirements.txt
```

If `python` is not the correct runtime, set its path in `.env`:

```env
SIMILARITY_PYTHON=C:/Path/To/python.exe
SIMILARITY_PYTHONPATH=C:/Path/To/Python/site-packages
```

Install Poppler and add its `bin` folder to your system `PATH`. If it is not on `PATH`, configure the two executable paths in `.env`:

```env
PDFTOPPM_BINARY=C:/Path/To/pdftoppm.exe
PDFINFO_BINARY=C:/Path/To/pdfinfo.exe
```

## Manuscript Archive

The PDFs are not included in Git. Download and extract `CapstoneLens-manuscripts.zip` from the [shared Google Drive folder](https://drive.google.com/drive/folders/1sSFCDO9DTue0UmlgzjI9OPw6qBBJjekE).

Import the extracted PDFs into your local repository:

```powershell
php artisan manuscripts:import "C:\path\to\extracted-papers"
```

The import safely skips duplicate documents. Imported PDFs and generated similarity data stay in private local storage and are never committed.

## Run

```powershell
.\serve-local.ps1
```

Open [http://127.0.0.1:8080](http://127.0.0.1:8080).

The first similarity evaluation downloads the required local transformer models. Later evaluations reuse the local cache.

## Verify

```powershell
php artisan test --compact
vendor\bin\pint --test --format agent
npm run build
```
