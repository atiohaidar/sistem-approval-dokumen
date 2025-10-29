# Contributing to Sistem Approval Dokumen

Thank you for your interest in contributing to this project! This document provides guidelines for contributing.

## Table of Contents
1. [Code of Conduct](#code-of-conduct)
2. [Getting Started](#getting-started)
3. [Development Workflow](#development-workflow)
4. [Coding Standards](#coding-standards)
5. [Testing](#testing)
6. [Pull Request Process](#pull-request-process)
7. [Reporting Issues](#reporting-issues)

## Code of Conduct

### Our Pledge

We are committed to providing a welcoming and inclusive environment for all contributors, regardless of:
- Age
- Body size
- Disability
- Ethnicity
- Gender identity and expression
- Level of experience
- Nationality
- Personal appearance
- Race
- Religion
- Sexual identity and orientation

### Our Standards

**Positive behavior includes:**
- Using welcoming and inclusive language
- Being respectful of differing viewpoints
- Gracefully accepting constructive criticism
- Focusing on what is best for the community
- Showing empathy towards others

**Unacceptable behavior includes:**
- Trolling, insulting/derogatory comments
- Public or private harassment
- Publishing others' private information
- Other conduct which could reasonably be considered inappropriate

## Getting Started

### Prerequisites
- PHP 8.2+
- Node.js 20+
- Composer 2
- npm 10+
- Git

### Setup Development Environment

1. Fork the repository
2. Clone your fork:
```bash
git clone https://github.com/YOUR_USERNAME/sistem-approval-dokumen.git
cd sistem-approval-dokumen
```

3. Set up backend:
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed
```

4. Set up frontend:
```bash
cd ../frontend
npm install
cp .env.example .env
```

5. Run development servers:
```bash
# Backend (in backend directory)
php artisan serve

# Frontend (in frontend directory)
npm run dev
```

## Development Workflow

### Branch Naming Convention

- `feature/` - New features (e.g., `feature/add-notifications`)
- `bugfix/` - Bug fixes (e.g., `bugfix/fix-login-redirect`)
- `hotfix/` - Critical production fixes (e.g., `hotfix/security-patch`)
- `refactor/` - Code refactoring (e.g., `refactor/improve-performance`)
- `docs/` - Documentation updates (e.g., `docs/update-api-guide`)

### Commit Message Convention

Follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, no code change)
- `refactor`: Code refactoring
- `perf`: Performance improvements
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

**Examples:**
```bash
feat(auth): add two-factor authentication
fix(documents): resolve PDF download issue
docs(api): update authentication endpoints
refactor(approval): simplify approval logic
test(documents): add integration tests
```

## Coding Standards

### Backend (PHP/Laravel)

1. **Follow PSR-12 coding standard**
```bash
# Check code style
./vendor/bin/pint --test

# Fix code style
./vendor/bin/pint
```

2. **Use type hints**
```php
public function createDocument(Request $request): JsonResponse
{
    // ...
}
```

3. **Write descriptive variable names**
```php
// Good
$approvedDocuments = Document::where('status', 'approved')->get();

// Bad
$docs = Document::where('status', 'approved')->get();
```

4. **Use early returns**
```php
// Good
public function update(Request $request, Document $document): JsonResponse
{
    if ($document->created_by !== Auth::id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $document->update($request->all());
    return response()->json($document);
}
```

5. **Add PHPDoc comments for complex methods**
```php
/**
 * Process document approval with multi-level logic
 *
 * @param Document $document The document to approve
 * @param User $approver The user approving the document
 * @return bool True if approval advances level, false otherwise
 * @throws \Exception If user is not authorized to approve
 */
public function processApproval(Document $document, User $approver): bool
{
    // ...
}
```

### Frontend (Vue.js/TypeScript)

1. **Use TypeScript for type safety**
```typescript
interface Document {
  id: number;
  title: string;
  status: 'draft' | 'pending_approval' | 'approved' | 'rejected';
}
```

2. **Use composition API**
```typescript
<script setup lang="ts">
const documents = ref<Document[]>([]);
const loading = ref(false);

const fetchDocuments = async () => {
  loading.value = true;
  try {
    documents.value = await useDocuments().fetchAll();
  } finally {
    loading.value = false;
  }
};
</script>
```

3. **Follow Vue.js naming conventions**
- Components: PascalCase (`DocumentCard.vue`)
- Props: camelCase (`documentId`)
- Events: kebab-case (`@update-status`)

4. **Use composables for reusable logic**
```typescript
// composables/useDocuments.ts
export const useDocuments = () => {
  const documents = ref<Document[]>([]);
  
  const fetchAll = async () => {
    // ...
  };
  
  return { documents, fetchAll };
};
```

5. **Add comments for complex logic**
```typescript
// Calculate approval progress considering parallel and sequential levels
const calculateProgress = (document: Document): number => {
  // Level progress is calculated as: 
  // (completed_levels / total_levels) * 100
  return (document.current_level / document.approvers.length) * 100;
};
```

### Database

1. **Always use migrations for schema changes**
```bash
php artisan make:migration add_status_to_documents_table
```

2. **Add indexes for frequently queried columns**
```php
$table->index('status');
$table->index(['user_id', 'created_at']);
```

3. **Use foreign key constraints**
```php
$table->foreignId('user_id')->constrained()->onDelete('cascade');
```

4. **Provide rollback for all migrations**
```php
public function down(): void
{
    Schema::dropIfExists('documents');
}
```

## Testing

### Backend Testing

1. **Write tests for all new features**
```php
public function test_user_can_create_document(): void
{
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->postJson('/api/documents', [
        'title' => 'Test Document',
        'file' => UploadedFile::fake()->create('test.pdf', 1024),
    ]);
    
    $response->assertStatus(201);
    $this->assertDatabaseHas('documents', ['title' => 'Test Document']);
}
```

2. **Run tests before committing**
```bash
php artisan test
```

3. **Aim for high test coverage**
```bash
php artisan test --coverage
```

### Frontend Testing

1. **Test component behavior**
```typescript
describe('DocumentCard', () => {
  it('displays document title', () => {
    const wrapper = mount(DocumentCard, {
      props: { document: { title: 'Test Doc' } }
    });
    expect(wrapper.text()).toContain('Test Doc');
  });
});
```

## Pull Request Process

### Before Submitting

1. **Update documentation** if you've added/changed functionality
2. **Add/update tests** for your changes
3. **Run code style checks**
   ```bash
   # Backend
   cd backend && ./vendor/bin/pint
   
   # Frontend (if configured)
   cd frontend && npm run lint
   ```
4. **Run all tests**
   ```bash
   # Backend
   cd backend && php artisan test
   ```
5. **Update CHANGELOG** if applicable

### Submitting Pull Request

1. **Create a descriptive PR title**
   - Good: "Add email notifications for document approvals"
   - Bad: "Update code"

2. **Fill out the PR template**
   - Description of changes
   - Related issue number
   - Type of change (feature, bugfix, etc.)
   - Checklist completion

3. **Request review** from maintainers

4. **Respond to feedback** promptly and professionally

5. **Keep PR focused** - one feature/fix per PR

### PR Review Criteria

- Code follows project coding standards
- All tests pass
- Documentation is updated
- No merge conflicts
- Changes are backward compatible (or migration path provided)
- Security implications considered

## Reporting Issues

### Bug Reports

Include:
1. **Clear title** describing the issue
2. **Steps to reproduce** the bug
3. **Expected behavior**
4. **Actual behavior**
5. **Environment details** (OS, PHP version, browser, etc.)
6. **Screenshots/logs** if applicable

Example:
```markdown
## Bug: Document download fails for large PDFs

### Steps to Reproduce
1. Upload PDF larger than 5MB
2. Click download button
3. Observe error

### Expected Behavior
PDF should download successfully

### Actual Behavior
Browser shows "Network Error"

### Environment
- OS: Ubuntu 22.04
- PHP: 8.2.0
- Browser: Chrome 120
- File size: 8MB
```

### Feature Requests

Include:
1. **Clear description** of the feature
2. **Use case** - why is this needed?
3. **Proposed solution** (optional)
4. **Alternatives considered** (optional)

Example:
```markdown
## Feature Request: Email notifications

### Description
Send email notifications when documents require approval

### Use Case
Approvers need to be notified immediately when a document awaits their review

### Proposed Solution
- Add notification system using Laravel Mail
- Configure SMTP settings
- Add notification preferences to user settings
```

## Security Issues

**DO NOT** create public issues for security vulnerabilities.

Instead:
1. Email: security@your-domain.com
2. Provide detailed description
3. Allow reasonable time for fix before disclosure

## Questions?

- Open a discussion on GitHub Discussions
- Check existing issues and documentation
- Contact maintainers

## License

By contributing, you agree that your contributions will be licensed under the same license as the project (MIT License).

---

Thank you for contributing! 🎉
