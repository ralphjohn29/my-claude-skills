# Supabase API Reference

Complete reference documentation for Supabase JavaScript/TypeScript client.

## Table of Contents

1. [Client Configuration](#client-configuration)
2. [Authentication API](#authentication-api)
3. [Database API](#database-api)
4. [Realtime API](#realtime-api)
5. [Storage API](#storage-api)
6. [Error Handling](#error-handling)
7. [Type Definitions](#type-definitions)
8. [Environment Variables](#environment-variables)
9. [Performance Tuning](#performance-tuning)
10. [Security Checklist](#security-checklist)

## Client Configuration

### createClient()

Creates a new Supabase client instance.

**Signature:**
```typescript
function createClient<Database = any>(
  supabaseUrl: string,
  supabaseKey: string,
  options?: SupabaseClientOptions
): SupabaseClient<Database>
```

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `supabaseUrl` | `string` | Yes | Your Supabase project URL |
| `supabaseKey` | `string` | Yes | Your Supabase anon or service role key |
| `options` | `SupabaseClientOptions` | No | Configuration options |

**Options:**

```typescript
interface SupabaseClientOptions {
  db?: {
    schema: string // Default: 'public'
  }
  auth?: {
    autoRefreshToken?: boolean // Default: true
    persistSession?: boolean // Default: true
    detectSessionInUrl?: boolean // Default: true
    flowType?: 'pkce' | 'implicit' // Default: 'pkce'
    storage?: Storage // Custom storage implementation
    storageKey?: string // Default: 'sb-auth-token'
  }
  global?: {
    headers?: Record<string, string>
    fetch?: typeof fetch
  }
  realtime?: {
    params?: {
      eventsPerSecond?: number // Default: 10
    }
    timeout?: number // Default: 10000
    heartbeatInterval?: number // Default: 30000
  }
}
```

**Returns:** `SupabaseClient<Database>`

**Example:**
```typescript
import { createClient } from '@supabase/supabase-js'

const supabase = createClient(
  'https://xyzcompany.supabase.co',
  'public-anon-key',
  {
    auth: {
      autoRefreshToken: true,
      persistSession: true
    },
    global: {
      headers: {
        'X-Application-Name': 'MyApp'
      }
    }
  }
)
```

## Authentication API

### supabase.auth

Authentication namespace for all auth-related operations.

#### signUp()

Create a new user account.

**Signature:**
```typescript
function signUp(credentials: {
  email: string
  password: string
  options?: {
    data?: object
    emailRedirectTo?: string
    captchaToken?: string
  }
}): Promise<AuthResponse>
```

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `email` | `string` | Yes | User's email address |
| `password` | `string` | Yes | User's password |
| `options.data` | `object` | No | Additional user metadata |
| `options.emailRedirectTo` | `string` | No | URL to redirect after email confirmation |
| `options.captchaToken` | `string` | No | Captcha token for bot protection |

**Returns:** `Promise<AuthResponse>`

```typescript
type AuthResponse = {
  data: {
    user: User | null
    session: Session | null
  }
  error: AuthError | null
}
```

#### signInWithPassword()

Sign in with email and password.

**Signature:**
```typescript
function signInWithPassword(credentials: {
  email: string
  password: string
}): Promise<AuthResponse>
```

#### signInWithOtp()

Sign in with one-time password (email or phone).

**Signature:**
```typescript
function signInWithOtp(credentials: {
  email?: string
  phone?: string
  options?: {
    emailRedirectTo?: string
    shouldCreateUser?: boolean
    data?: object
    channel?: 'sms' | 'whatsapp'
  }
}): Promise<AuthOtpResponse>
```

#### signInWithOAuth()

Sign in with OAuth provider.

**Signature:**
```typescript
function signInWithOAuth(credentials: {
  provider: Provider
  options?: {
    redirectTo?: string
    scopes?: string
    queryParams?: Record<string, string>
  }
}): Promise<OAuthResponse>
```

**Supported Providers:**
- `apple`, `google`, `github`, `gitlab`, `bitbucket`
- `discord`, `facebook`, `twitter`, `microsoft`
- `linkedin`, `notion`, `slack`, `spotify`, `twitch`
- `workos`, `zoom`, and more

#### signOut()

Sign out the current user.

**Signature:**
```typescript
function signOut(): Promise<{ error: AuthError | null }>
```

#### getSession()

Get the current session.

**Signature:**
```typescript
function getSession(): Promise<{
  data: { session: Session | null }
  error: AuthError | null
}>
```

#### getUser()

Get the current user.

**Signature:**
```typescript
function getUser(jwt?: string): Promise<{
  data: { user: User | null }
  error: AuthError | null
}>
```

#### updateUser()

Update user data.

**Signature:**
```typescript
function updateUser(attributes: {
  email?: string
  password?: string
  phone?: string
  data?: object
}): Promise<UserResponse>
```

#### refreshSession()

Manually refresh the session.

**Signature:**
```typescript
function refreshSession(): Promise<AuthResponse>
```

#### resetPasswordForEmail()

Send password reset email.

**Signature:**
```typescript
function resetPasswordForEmail(
  email: string,
  options?: {
    redirectTo?: string
    captchaToken?: string
  }
): Promise<{ data: object; error: AuthError | null }>
```

#### onAuthStateChange()

Listen to auth state changes.

**Signature:**
```typescript
function onAuthStateChange(
  callback: (event: AuthChangeEvent, session: Session | null) => void
): { data: { subscription: Subscription } }
```

**Events:**
- `SIGNED_IN` - User signed in
- `SIGNED_OUT` - User signed out
- `TOKEN_REFRESHED` - Session token refreshed
- `USER_UPDATED` - User data updated
- `PASSWORD_RECOVERY` - Password recovery initiated

#### MFA Methods

**enroll()** - Enroll in MFA:
```typescript
function enroll(params: {
  factorType: 'totp'
  friendlyName?: string
}): Promise<AuthMFAEnrollResponse>
```

**challenge()** - Create MFA challenge:
```typescript
function challenge(params: {
  factorId: string
}): Promise<AuthMFAChallengeResponse>
```

**verify()** - Verify MFA code:
```typescript
function verify(params: {
  factorId: string
  challengeId?: string
  code: string
}): Promise<AuthMFAVerifyResponse>
```

**unenroll()** - Unenroll from MFA:
```typescript
function unenroll(params: {
  factorId: string
}): Promise<AuthMFAUnenrollResponse>
```

**listFactors()** - List enrolled factors:
```typescript
function listFactors(): Promise<{
  data: { all: Factor[]; totp: Factor[] }
  error: AuthError | null
}>
```

## Database API

### supabase.from()

Create a query builder for a table.

**Signature:**
```typescript
function from<T = any>(table: string): PostgrestQueryBuilder<T>
```

#### SELECT Queries

**select()** - Fetch data:
```typescript
function select(
  columns?: string,
  options?: {
    head?: boolean
    count?: 'exact' | 'planned' | 'estimated' | null
  }
): PostgrestFilterBuilder
```

**Filter Methods:**

| Method | Description | Example |
|--------|-------------|---------|
| `eq(column, value)` | Equal to | `.eq('status', 'active')` |
| `neq(column, value)` | Not equal to | `.neq('role', 'admin')` |
| `gt(column, value)` | Greater than | `.gt('age', 18)` |
| `gte(column, value)` | Greater than or equal | `.gte('score', 80)` |
| `lt(column, value)` | Less than | `.lt('price', 100)` |
| `lte(column, value)` | Less than or equal | `.lte('stock', 10)` |
| `like(column, pattern)` | Pattern match | `.like('email', '%@gmail.com')` |
| `ilike(column, pattern)` | Case-insensitive match | `.ilike('name', '%john%')` |
| `in(column, values)` | In array | `.in('id', [1, 2, 3])` |
| `is(column, value)` | Is exact value (null) | `.is('deleted_at', null)` |
| `not(column, operator, value)` | Negate condition | `.not('status', 'eq', 'banned')` |
| `or(query)` | OR condition | `.or('role.eq.admin,status.eq.vip')` |
| `filter(column, operator, value)` | Generic filter | `.filter('age', 'gte', 18)` |

**Modifier Methods:**

| Method | Description | Example |
|--------|-------------|---------|
| `order(column, options)` | Order results | `.order('created_at', { ascending: false })` |
| `limit(count)` | Limit results | `.limit(10)` |
| `range(from, to)` | Pagination | `.range(0, 9)` |
| `single()` | Return single object | `.select().eq('id', 1).single()` |
| `maybeSingle()` | Return single or null | `.select().eq('id', 1).maybeSingle()` |
| `csv()` | Return as CSV | `.select().csv()` |

**Joins:**
```typescript
// One-to-many
.select(`
  id,
  email,
  posts (id, title)
`)

// Many-to-many
.select(`
  id,
  user_roles (
    role:roles (name)
  )
`)

// Inner join (only rows with relation)
.select(`
  id,
  posts!inner (id, title)
`)
```

#### INSERT Operations

**insert()** - Insert rows:
```typescript
function insert(
  values: object | object[],
  options?: {
    defaultToNull?: boolean
  }
): PostgrestFilterBuilder
```

**upsert()** - Insert or update on conflict:
```typescript
function upsert(
  values: object | object[],
  options?: {
    onConflict?: string
    ignoreDuplicates?: boolean
    defaultToNull?: boolean
  }
): PostgrestFilterBuilder
```

#### UPDATE Operations

**update()** - Update rows:
```typescript
function update(
  values: object,
  options?: {
    count?: 'exact' | 'planned' | 'estimated' | null
  }
): PostgrestFilterBuilder
```

#### DELETE Operations

**delete()** - Delete rows:
```typescript
function delete(
  options?: {
    count?: 'exact' | 'planned' | 'estimated' | null
  }
): PostgrestFilterBuilder
```

#### RPC Calls

**rpc()** - Call PostgreSQL function:
```typescript
function rpc<T = any>(
  fn: string,
  params?: object,
  options?: {
    head?: boolean
    count?: 'exact' | 'planned' | 'estimated' | null
  }
): PostgrestFilterBuilder<T>
```

## Realtime API

### supabase.channel()

Create a realtime channel.

**Signature:**
```typescript
function channel(
  name: string,
  opts?: RealtimeChannelOptions
): RealtimeChannel
```

**Options:**
```typescript
interface RealtimeChannelOptions {
  config?: {
    broadcast?: {
      ack?: boolean
      self?: boolean
    }
    presence?: {
      key?: string
    }
  }
}
```

#### Subscribe to Database Changes

```typescript
channel.on(
  'postgres_changes',
  {
    event: '*' | 'INSERT' | 'UPDATE' | 'DELETE'
    schema: string
    table: string
    filter?: string
  },
  callback: (payload: RealtimePostgresChangesPayload) => void
)
```

**Payload Structure:**
```typescript
interface RealtimePostgresChangesPayload<T = any> {
  eventType: 'INSERT' | 'UPDATE' | 'DELETE'
  new: T // New record (INSERT, UPDATE)
  old: T // Old record (UPDATE, DELETE)
  schema: string
  table: string
  commit_timestamp: string
  errors: any[]
}
```

#### Broadcast Messages

**Send:**
```typescript
channel.send({
  type: 'broadcast'
  event: string
  payload: any
})
```

**Receive:**
```typescript
channel.on(
  'broadcast',
  { event: string },
  callback: (payload: any) => void
)
```

#### Presence Tracking

**Track presence:**
```typescript
channel.track(state: object): Promise<'ok' | 'timed_out' | 'error'>
```

**Get presence state:**
```typescript
channel.presenceState(): Record<string, Presence[]>
```

**Listen to presence:**
```typescript
channel.on(
  'presence',
  { event: 'sync' | 'join' | 'leave' },
  callback: (payload: PresencePayload) => void
)
```

**Untrack presence:**
```typescript
channel.untrack(): Promise<'ok' | 'timed_out' | 'error'>
```

#### Channel Lifecycle

**subscribe()** - Subscribe to channel:
```typescript
channel.subscribe(
  callback?: (status: 'SUBSCRIBED' | 'TIMED_OUT' | 'CLOSED' | 'CHANNEL_ERROR') => void
): RealtimeChannel
```

**unsubscribe()** - Unsubscribe from channel:
```typescript
channel.unsubscribe(): Promise<'ok' | 'timed_out' | 'error'>
```

## Storage API

### supabase.storage

Storage namespace for file operations.

#### Bucket Management

**listBuckets()** - List all buckets:
```typescript
function listBuckets(): Promise<{
  data: Bucket[] | null
  error: StorageError | null
}>
```

**getBucket()** - Get bucket details:
```typescript
function getBucket(id: string): Promise<{
  data: Bucket | null
  error: StorageError | null
}>
```

**createBucket()** - Create bucket:
```typescript
function createBucket(
  id: string,
  options?: {
    public?: boolean
    fileSizeLimit?: number
    allowedMimeTypes?: string[]
  }
): Promise<{
  data: { name: string } | null
  error: StorageError | null
}>
```

**updateBucket()** - Update bucket:
```typescript
function updateBucket(
  id: string,
  options: {
    public?: boolean
    fileSizeLimit?: number
    allowedMimeTypes?: string[]
  }
): Promise<{
  data: { message: string } | null
  error: StorageError | null
}>
```

**deleteBucket()** - Delete bucket:
```typescript
function deleteBucket(id: string): Promise<{
  data: { message: string } | null
  error: StorageError | null
}>
```

**emptyBucket()** - Remove all files from bucket:
```typescript
function emptyBucket(id: string): Promise<{
  data: { message: string } | null
  error: StorageError | null
}>
```

#### File Operations

**from()** - Access bucket:
```typescript
function from(id: string): StorageFileApi
```

**upload()** - Upload file:
```typescript
function upload(
  path: string,
  fileBody: File | Blob | ArrayBuffer | FormData,
  options?: {
    cacheControl?: string
    contentType?: string
    upsert?: boolean
    duplex?: string
    onUploadProgress?: (progress: { loaded: number; total: number }) => void
  }
): Promise<{
  data: { path: string; id: string; fullPath: string } | null
  error: StorageError | null
}>
```

**download()** - Download file:
```typescript
function download(path: string): Promise<{
  data: Blob | null
  error: StorageError | null
}>
```

**list()** - List files:
```typescript
function list(
  path?: string,
  options?: {
    limit?: number
    offset?: number
    sortBy?: {
      column: 'name' | 'created_at' | 'updated_at' | 'last_accessed_at'
      order: 'asc' | 'desc'
    }
    search?: string
  }
): Promise<{
  data: FileObject[] | null
  error: StorageError | null
}>
```

**remove()** - Delete files:
```typescript
function remove(paths: string[]): Promise<{
  data: FileObject[] | null
  error: StorageError | null
}>
```

**move()** - Move file:
```typescript
function move(
  fromPath: string,
  toPath: string
): Promise<{
  data: { message: string } | null
  error: StorageError | null
}>
```

**copy()** - Copy file:
```typescript
function copy(
  fromPath: string,
  toPath: string
): Promise<{
  data: { path: string } | null
  error: StorageError | null
}>
```

#### URL Generation

**getPublicUrl()** - Get public URL:
```typescript
function getPublicUrl(
  path: string,
  options?: {
    download?: boolean | string
    transform?: {
      width?: number
      height?: number
      resize?: 'cover' | 'contain' | 'fill'
      format?: 'origin' | 'webp' | 'avif'
      quality?: number
    }
  }
): {
  data: { publicUrl: string }
}
```

**createSignedUrl()** - Create signed URL:
```typescript
function createSignedUrl(
  path: string,
  expiresIn: number,
  options?: {
    download?: boolean | string
    transform?: {
      width?: number
      height?: number
      resize?: 'cover' | 'contain' | 'fill'
      format?: 'origin' | 'webp' | 'avif'
      quality?: number
    }
  }
): Promise<{
  data: { signedUrl: string; path: string } | null
  error: StorageError | null
}>
```

**createSignedUrls()** - Create multiple signed URLs:
```typescript
function createSignedUrls(
  paths: string[],
  expiresIn: number,
  options?: {
    download?: boolean | string
  }
): Promise<{
  data: Array<{
    path: string
    signedUrl: string
    error: string | null
  }> | null
  error: StorageError | null
}>
```

## Error Handling

### Error Types

**AuthError:**
```typescript
interface AuthError extends Error {
  status?: number
  code?: string
}
```

**PostgrestError:**
```typescript
interface PostgrestError {
  message: string
  details: string
  hint: string
  code: string
}
```

**StorageError:**
```typescript
interface StorageError extends Error {
  statusCode?: string
}
```

### Response Pattern

All Supabase operations follow this pattern:

```typescript
type SupabaseResponse<T> = {
  data: T | null
  error: Error | null
}
```

### Error Handling Strategies

**Option 1: Check error field**
```typescript
const { data, error } = await supabase
  .from('users')
  .select()

if (error) {
  console.error('Error:', error.message)
  return
}

// Use data safely
console.log(data)
```

**Option 2: Use throwOnError()**
```typescript
try {
  const { data } = await supabase
    .from('users')
    .insert({ email: 'user@example.com' })
    .throwOnError()

  console.log('Success:', data)
} catch (error) {
  console.error('Failed:', error)
}
```

### Common Error Codes

**Authentication:**
- `400` - Invalid credentials
- `422` - Email not confirmed
- `429` - Too many requests

**Database:**
- `23505` - Unique violation
- `23503` - Foreign key violation
- `42501` - Insufficient privilege (RLS)

**Storage:**
- `404` - File not found
- `413` - Payload too large
- `415` - Unsupported media type

## Type Definitions

### User

```typescript
interface User {
  id: string
  app_metadata: { [key: string]: any }
  user_metadata: { [key: string]: any }
  aud: string
  confirmation_sent_at?: string
  recovery_sent_at?: string
  email_change_sent_at?: string
  new_email?: string
  invited_at?: string
  action_link?: string
  email?: string
  phone?: string
  created_at: string
  confirmed_at?: string
  email_confirmed_at?: string
  phone_confirmed_at?: string
  last_sign_in_at?: string
  role?: string
  updated_at?: string
  identities?: UserIdentity[]
}
```

### Session

```typescript
interface Session {
  access_token: string
  refresh_token: string
  expires_in: number
  expires_at?: number
  token_type: string
  user: User
}
```

### FileObject

```typescript
interface FileObject {
  name: string
  id: string | null
  updated_at: string | null
  created_at: string | null
  last_accessed_at: string | null
  metadata: {
    eTag: string
    size: number
    mimetype: string
    cacheControl: string
    lastModified: string
    contentLength: number
    httpStatusCode: number
  }
}
```

## Environment Variables

### Required Variables

```bash
# Client-side (safe to expose)
NEXT_PUBLIC_SUPABASE_URL=https://xyzcompany.supabase.co
NEXT_PUBLIC_SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...

# Server-side only (keep secure!)
SUPABASE_SERVICE_ROLE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### Optional Variables

```bash
# Database connection (for direct PostgreSQL access)
SUPABASE_DB_URL=postgresql://postgres:[PASSWORD]@db.xyzcompany.supabase.co:5432/postgres

# JWT secret (for custom JWT verification)
SUPABASE_JWT_SECRET=your-jwt-secret-here
```

## Performance Tuning

### Database Optimization

**1. Add Indexes:**
```sql
-- Index frequently queried columns
CREATE INDEX idx_users_email ON users(email);

-- Index foreign keys
CREATE INDEX idx_posts_user_id ON posts(user_id);

-- Composite index for multi-column queries
CREATE INDEX idx_posts_user_published ON posts(user_id, published) WHERE published = true;

-- Partial index for common filters
CREATE INDEX idx_active_users ON users(status) WHERE status = 'active';
```

**2. Use Appropriate Query Patterns:**
```typescript
// Bad: Fetch all columns
const { data } = await supabase.from('users').select()

// Good: Only fetch needed columns
const { data } = await supabase
  .from('users')
  .select('id, email')
```

**3. Implement Pagination:**
```typescript
// Bad: Fetch all rows
const { data } = await supabase.from('posts').select()

// Good: Paginate results
const { data } = await supabase
  .from('posts')
  .select()
  .range(0, 9)
  .order('created_at', { ascending: false })
```

**4. Use Count Wisely:**
```typescript
// For large tables, use estimated count
const { count } = await supabase
  .from('users')
  .select('*', { count: 'estimated', head: true })

// For exact count only when necessary
const { count } = await supabase
  .from('small_table')
  .select('*', { count: 'exact', head: true })
```

### Realtime Optimization

**1. Use Filters:**
```typescript
// Bad: Subscribe to all changes
channel.on('postgres_changes', { event: '*', schema: 'public', table: 'posts' }, handler)

// Good: Filter by relevant rows
channel.on(
  'postgres_changes',
  { event: '*', schema: 'public', table: 'posts', filter: `user_id=eq.${userId}` },
  handler
)
```

**2. Clean Up Subscriptions:**
```typescript
useEffect(() => {
  const channel = supabase.channel('my-channel')
  channel.subscribe()

  // Always clean up
  return () => {
    channel.unsubscribe()
  }
}, [])
```

### Storage Optimization

**1. Use Image Transformations:**
```typescript
// Generate responsive images
const thumbnailUrl = supabase.storage
  .from('photos')
  .getPublicUrl('photo.jpg', {
    transform: {
      width: 200,
      height: 200,
      quality: 80,
      format: 'webp'
    }
  })
```

**2. Set Appropriate Cache Headers:**
```typescript
await supabase.storage
  .from('assets')
  .upload('file.jpg', file, {
    cacheControl: '31536000' // 1 year
  })
```

## Security Checklist

### Authentication

- [ ] Never expose service role key in client-side code
- [ ] Use anon key for client applications
- [ ] Enable email confirmation for sign-ups
- [ ] Implement rate limiting for auth endpoints
- [ ] Use PKCE flow for OAuth
- [ ] Enable MFA for sensitive applications
- [ ] Set appropriate password requirements
- [ ] Implement account lockout after failed attempts

### Database

- [ ] Enable Row-Level Security (RLS) on all tables
- [ ] Create appropriate RLS policies for each table
- [ ] Test RLS policies with different user contexts
- [ ] Never bypass RLS in client-side code
- [ ] Use prepared statements (automatic with Supabase)
- [ ] Validate user input on both client and server
- [ ] Implement database constraints (NOT NULL, UNIQUE, CHECK)
- [ ] Use database functions for complex operations

### Storage

- [ ] Set appropriate bucket policies
- [ ] Use RLS for storage.objects table
- [ ] Validate file types and sizes
- [ ] Scan uploaded files for malware
- [ ] Use signed URLs for private files
- [ ] Set appropriate CORS policies
- [ ] Implement file size limits
- [ ] Use CDN for public assets

### General

- [ ] Use environment variables for all secrets
- [ ] Never commit credentials to version control
- [ ] Implement proper error handling (don't leak sensitive info)
- [ ] Use HTTPS for all connections
- [ ] Enable database backups
- [ ] Monitor auth logs for suspicious activity
- [ ] Keep Supabase client library updated
- [ ] Implement proper CORS configuration
- [ ] Use Content Security Policy (CSP) headers
- [ ] Regularly audit RLS policies

---

**Reference Version**: 1.0.0
**Last Updated**: October 2025
**For Examples**: See EXAMPLES.md
**For Guides**: See SKILL.md
