# FileStorageService Tests - Learnings

## Test Patterns for Service Tests

1. **File System Mocking**: Use `Storage::fake('uploads')` in setUp() for all file operations
2. **Database Transactions**: Use `DatabaseTransactions` trait for test isolation
3. **Factory Usage**: Create test data with `Model::factory()->create()`
4. **UploadedFile Creation**:
   - Images: `UploadedFile::fake()->image('name.jpg')`
   - PDFs: `UploadedFile::fake()->create('name.pdf', size, 'application/pdf')`

## PDF Filename Bug Fix Verification

The critical bug fix on line 104 of FileStorageService uses dot concatenation:
```php
$filename = $pdfName.'.'.$extension;  // ✓ Correct
// NOT: $filename = $pdfName.$extension;  // ✗ Would produce 'diplomski123pdf'
```

Test this explicitly with assertions on the returned filename format.

## Handling File Replacement Scenarios

When testing file replacement with fake storage:
- Both old and new files with same base name + extension will produce the same filename
- This is expected behavior - the new file overwrites the old one
- Don't test that old file "doesn't exist" after replacement with same extension
- Instead, verify: old file deleted OR new file exists (both prove replacement occurred)

## Test Method Organization

Structure tests by method:
- Happy path tests (basic functionality)
- Error handling tests (invalid input)
- Edge cases (empty values, missing files)
- Data integrity tests (record updates)
- Return value verification

This provides comprehensive coverage without redundancy.
