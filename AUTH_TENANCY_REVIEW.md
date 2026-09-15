# Authentication, Tenancy, and Permissions Review

Date: 2026-09-15  
Scope: Current checked-out code reviewed against the requirements below. This is a requirements review, not a commit-diff review.

## Requirements

### Super Admin

- Login with email, password, and a reCAPTCHA token.
- Create multiple businesses with `name`, `email`, `phone`, `address`, and `status`.
- Automatically create a tenant database and its tables for each new business.
- Create owners with `first_name`, `last_name`, `email`, `phone`, `password`, `password_confirmation`, `status`, `is_active`, and `business_ids`.
- Assign an owner to multiple businesses.
- Create roles with `name` and `tenant_id`; roles must be independent for each tenant.
- Create permissions with `name` and `type`, such as `supplier` with `create`, `view`, `update`, or `delete`.

### Owner and User

- Login with email, password, and a reCAPTCHA token.
- Select or switch an assigned business using `tenant_id`.
- Add users with `name`, `email`, `password`, `tenant_id`, `role_id`, and `permission_ids`.
- Give owners full access within their assigned businesses.
- Restrict ordinary users according to their permissions.

## Overall Assessment

The implementation does not yet satisfy the requirements. The main gaps concern authentication, tenant isolation, action-level permissions, and individual user permissions.

Automatic tenant database creation and migrations are wired through `TenancyServiceProvider`, but provisioning was not exercised during this review.

## Findings

Priorities: **P1** means an urgent defect; **P2** means an ordinary defect that should be fixed. Line references describe the reviewed snapshot and may shift after edits.

### 1. [P1] Authenticate owner-management routes

Location: [routes/api.php](routes/api.php), line 60.

All 15 `/api/owner/*` routes lack authentication. Anonymous callers can list users, change passwords, delete users, assign businesses, and replace role permissions. Controllers and request authorization add no protection.

### 2. [P1] Check both permission name and type

Location: [CheckPermission.php](app/Http/Middleware/CheckPermission.php), line 115.

The middleware checks only permission `name` and ignores the action argument. A user with `supplier:view` consequently passes supplier create, update, and delete checks.

### 3. [P1] Restrict mobile business administration

Location: [routes/mobile.php](routes/mobile.php), line 90.

Any authenticated tenant user can access the Super Admin business controller. Its queries use the central database without ownership restrictions, allowing users to read, modify, or delete other businesses by ID.

### 4. [P1] Authorize role-permission assignment

Location: [routes/mobile.php](routes/mobile.php), line 247.

Any authenticated user with a selected business can replace permissions on any role ID. There is no permission check or tenant restriction, allowing self-escalation and changes to another business's roles.

### 5. [P1] Scope user management to authorized businesses

Location: [UserService.php](app/Services/User/UserService.php), line 111.

User lookups and mutations use unrestricted IDs. Business assignment checks whether the role belongs to the submitted business, but never whether the caller controls that business. Adding authentication alone will still leave cross-business user management exposed.

### 6. [P1] Protect remaining tenant operations with permissions

Location: [routes/tenant.php](routes/tenant.php), line 107.

Balance, bank-account, invoice-item, and other operations lack permission middleware. Their controllers do not enforce equivalent authorization, so users without those permissions can perform changes. Mobile routes have the same gaps.

### 7. [P1] Revalidate business access on each request

Location: [InitializeTenantFromSession.php](app/Http/Middleware/InitializeTenantFromSession.php), line 23.

Session initialization checks only that the tenant exists. Removing an owner's business assignment or disabling the business does not invalidate existing access. Token initialization similarly skips ownership checks for owners, and ordinary-user business selection does not require an active business.

### 8. [P1] Verify reCAPTCHA during login

Location: [AdminLoginController.php](app/Http/Controllers/Admin/Auth/AdminLoginController.php), line 14.

Both admin and owner/user login accept any nonempty `token`. Actual reCAPTCHA verification happens only in a separate optional endpoint, which callers can bypass.

### 9. [P1] Pin the shared User model to the central database

Location: [User.php](app/Models/User/User.php), line 11.

The model inherits the default connection. After tenant initialization, mobile user management queries tenant-local `users`, while memberships and roles remain central. This can target unrelated local users or fail against the incompatible tenant schema.

### 10. [P2] Remove global role-name uniqueness

Location: [Role tenant migration](database/migrations/2026_09_04_141051_add_tenant_id_to_roles_table.php), line 20.

The migration adds tenant-specific uniqueness without dropping the existing `(name, guard_name)` constraint. Creating `Manager` in a second business fails. This was reproduced using the actual migrations in an in-memory database.

### 11. [P2] Persist individual user permissions

Location: [UserRequest.php](app/Http/Requests/User/UserRequest.php), line 32.

`permission_ids` has no validation rule and is discarded by `validated()`. Creation never saves it, and authorization reads only role permissions. The requested per-user permission assignment is therefore missing.

### 12. [P2] Preserve the supplied owner status

Location: [OwnerUserService.php](app/Services/OwnerUser/OwnerUserService.php), line 71.

Creation forcibly sets `is_active=true` and `status=approved`. An owner explicitly submitted as inactive or suspended becomes active and approved.

### 13. [P2] Recognize the current owner login and business relationship

Location: [EnsureTenantOwner.php](app/Http/Middleware/EnsureTenantOwner.php), line 16.

This middleware checks the `web` guard and legacy `tenant.owner_user_id`. Current owner login uses the `owner` guard and `business_owners` assignments, so valid owners receive 403 responses for site settings.

### 14. [P2] Connect tenant user creation to the required account flow

Location: [routes/tenant.php](routes/tenant.php), line 226.

`/api/user` still invokes the legacy tenant controller. It requires `first_name` and `last_name` and discards password, tenant, role, and permission fields. It cannot create the shared login account and business membership described in the requirements.

### 15. [P2] Correct the user-update argument order

Location: [Tenant UserController.php](app/Http/Controllers/Tenant/User/UserController.php), line 41.

The controller passes `(validatedData, id)` to a service expecting `(string id, array data)`. Valid update requests fail with a `TypeError`.

### 16. [P2] Correct the credit middleware alias

Location: [routes/tenant.php](routes/tenant.php), line 133.

`permission.typecredit,view` is not registered. Credit-detail requests fail when Laravel resolves the middleware, including requests from owners.

## Documentation Review

At review time, [README.md](README.md) contained only Laravel's default documentation. It did not document the application's API contracts, business switching, provisioning, or permission rules.

This report records requirements and defects. It does not establish a completed API contract or imply that the findings have been fixed.

## Validation and Limits

- Inspected routes, controllers, requests, services, models, middleware, migrations, and existing tests relevant to the requirements.
- Confirmed registered middleware for owner-management and mobile business routes with `php artisan route:list`.
- Ran `vendor/bin/phpunit --do-not-cache-result`: **13 tests passed, 49 assertions**.
- Reproduced the cross-tenant role-name uniqueness failure using the actual role migrations in an in-memory SQLite database.
- Confirmed that the shared User model resolves to the tenant connection when it is the default connection.
- Confirmed that `UserRequest` has no `permission_ids` validation rule.
- Existing tests do not cover the reviewed authentication and tenant-permission flows. Passing tests do not establish that these requirements are satisfied.
- No live tenant provisioning or production verification was performed.
- The review did not modify application code.
