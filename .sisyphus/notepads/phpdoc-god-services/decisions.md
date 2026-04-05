# Decisions - PHPDoc for God Services

## 1. PHPDoc Format
Selected a verbose format for the class-level PHPDoc to emphasize the "God Service" nature and provide a clear overview of responsibilities, following the user's template.

## 2. Technical Debt Tagging
Used `NOTE:` tags in method documentation to explicitly call out legacy issues like tight coupling to the `Request` object instead of using DTOs exclusively.

## 3. Prioritization
Focused on the most complex methods (CRUD, mass operations, and data retrieval) as requested, ensuring the core public API is well-documented.
