# Supabase MCP Integration Skill

## Overview

This skill provides comprehensive guidance for building production-ready applications using Supabase - the open-source Backend-as-a-Service platform built on PostgreSQL. Supabase positions itself as "the Firebase alternative" with a focus on PostgreSQL, developer experience, and open-source principles.

## What is Supabase?

Supabase is an integrated platform that combines:

- **PostgreSQL Database** - Full Postgres with extensions (PostGIS, pg_vector)
- **Authentication** - JWT-based auth with 20+ OAuth providers, magic links, MFA
- **Auto-generated APIs** - Instant REST and GraphQL APIs from database schema
- **Realtime** - WebSocket server for database changes, broadcast, and presence
- **Storage** - S3-compatible file storage with CDN and image optimization
- **Edge Functions** - Globally distributed serverless functions (Deno runtime)

## When to Use This Skill

Use this skill when you need to:

1. **Build Full-Stack Applications**
   - Web applications with React, Next.js, Vue, Angular
   - Mobile applications with React Native, Flutter
   - Server-side applications with Node.js, Deno

2. **Implement Authentication**
   - Email/password authentication
   - Social login (Google, GitHub, etc.)
   - Magic links and OTP
   - Multi-factor authentication (MFA)
   - Session management

3. **Work with PostgreSQL**
   - Type-safe database operations
   - Complex queries with joins and filters
   - Database-level authorization (RLS)
   - Real-time database subscriptions

4. **Manage File Storage**
   - Upload and serve files
   - Image optimization and transformation
   - Public and private buckets
   - CDN delivery

5. **Build Real-Time Features**
   - Live chat and messaging
   - Collaborative editing
   - Live dashboards and analytics
   - Multiplayer games
   - Presence tracking

6. **Create Multi-Tenant Applications**
   - SaaS platforms with data isolation
   - Role-based access control
   - Organization-based permissions

## Key Features

### 🔐 Authentication

- **Multiple Auth Methods**: Email/password, magic links, phone OTP, OAuth (20+ providers)
- **Session Management**: JWT-based with automatic refresh
- **User Metadata**: Store custom user data
- **MFA Support**: Time-based one-time passwords (TOTP)
- **Social Login**: Google, GitHub, Apple, and more

### 🗄️ Database

- **Full PostgreSQL**: All Postgres features including views, functions, triggers
- **Type-Safe**: Automatic TypeScript type generation from schema
- **Query Builder**: Intuitive API for complex queries
- **Relationships**: One-to-many, many-to-many with automatic joins
- **RPC Calls**: Execute PostgreSQL functions from client

### ⚡ Realtime

- **Database Changes**: Subscribe to INSERT, UPDATE, DELETE events
- **Broadcast**: Low-latency messaging between clients
- **Presence**: Track online users and state synchronization
- **Filters**: Subscribe to specific rows based on conditions

### 📦 Storage

- **File Management**: Upload, download, move, copy, delete files
- **Image Transformation**: Automatic resizing, format conversion, optimization
- **CDN Delivery**: Global CDN with 285+ cities
- **RLS for Files**: Database-level security for file access
- **Resumable Uploads**: TUS protocol for large files

### 🛡️ Security

- **Row-Level Security (RLS)**: Database-level authorization
- **JWT Integration**: Automatic token inclusion in queries
- **Policy-Based Access**: Fine-grained control at row and column level
- **Multi-Tenant Patterns**: Built-in support for SaaS applications

### 🎯 TypeScript

- **Type Generation**: Automatic types from database schema
- **Type-Safe Queries**: Compile-time error detection
- **IDE Support**: Full autocomplete and IntelliSense
- **Helper Types**: Extract table, insert, update types

## Quick Start

### Installation

```bash
npm install @supabase/supabase-js
```

### Basic Setup

```typescript
import { createClient } from '@supabase/supabase-js'

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL!,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!
)

// Sign up
const { data, error } = await supabase.auth.signUp({
  email: 'user@example.com',
  password: 'secure-password'
})

// Query data
const { data: users } = await supabase
  .from('users')
  .select('id, email, created_at')

// Realtime subscription
const channel = supabase
  .channel('posts')
  .on('postgres_changes',
    { event: 'INSERT', schema: 'public', table: 'posts' },
    (payload) => console.log('New post:', payload.new)
  )
  .subscribe()

// Upload file
const { data: uploadData } = await supabase.storage
  .from('avatars')
  .upload(`${userId}/avatar.jpg`, file)
```

## Skill File Organization

This skill includes the following files:

- **SKILL.md** - Comprehensive reference covering all Supabase features (25KB+)
  - Client setup and configuration
  - Authentication methods and patterns
  - Database operations (SELECT, INSERT, UPDATE, DELETE)
  - Realtime subscriptions
  - Storage operations
  - TypeScript integration
  - Row-Level Security (RLS)
  - Best practices and troubleshooting

- **EXAMPLES.md** - Practical code examples (18KB+)
  - Authentication flows (15+ examples)
  - Database queries (20+ examples)
  - Realtime patterns (10+ examples)
  - Storage operations (10+ examples)
  - RLS policies (15+ examples)
  - Full application examples

- **REFERENCE.md** - Complete API reference (15KB+)
  - All Supabase client methods
  - Configuration options
  - Type definitions
  - Error codes
  - Performance tuning
  - Security checklist

- **README.md** - This file, providing overview and quick start

## Progressive Disclosure

This skill uses progressive disclosure - start with SKILL.md for core concepts and common patterns, then dive into EXAMPLES.md for practical implementations and REFERENCE.md for complete API details.

## Architecture Pattern

### Client Initialization (Singleton)

```typescript
// lib/supabase.ts
import { createClient, SupabaseClient } from '@supabase/supabase-js'
import { Database } from './database.types'

let supabaseInstance: SupabaseClient<Database> | null = null

export function getSupabaseClient(): SupabaseClient<Database> {
  if (!supabaseInstance) {
    supabaseInstance = createClient<Database>(
      process.env.NEXT_PUBLIC_SUPABASE_URL!,
      process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!,
      {
        auth: {
          autoRefreshToken: true,
          persistSession: true,
          detectSessionInUrl: true
        }
      }
    )
  }
  return supabaseInstance
}

export const supabase = getSupabaseClient()
```

### Type-Safe Development

```typescript
// Generate types from database
// $ supabase gen types typescript --project-id YOUR_ID > database.types.ts

import { Database } from './database.types'

const supabase = createClient<Database>(url, key)

// All queries are now type-safe
const { data } = await supabase
  .from('users')  // TypeScript knows this table exists
  .select('id, email')  // TypeScript validates column names
```

### Row-Level Security Pattern

```sql
-- Enable RLS
ALTER TABLE posts ENABLE ROW LEVEL SECURITY;

-- Users can only see their own posts
CREATE POLICY "Users can only see own posts"
ON posts FOR SELECT TO authenticated
USING (auth.uid() = user_id);

-- Users can only insert their own posts
CREATE POLICY "Users can only insert own posts"
ON posts FOR INSERT TO authenticated
WITH CHECK (auth.uid() = user_id);
```

## Common Use Cases

### 1. Todo Application with Real-Time Sync

- User authentication
- CRUD operations with RLS
- Real-time updates across devices
- Type-safe database queries

### 2. Chat Application

- User authentication and presence
- Real-time message delivery
- Typing indicators (broadcast)
- Online user tracking (presence)
- File attachments (storage)

### 3. SaaS Multi-Tenant Application

- Organization-based data isolation
- Role-based access control
- User invitation system
- Billing and subscription management

### 4. Social Media Platform

- User profiles and authentication
- Posts with likes and comments
- Real-time notifications
- Image uploads with transformation
- Infinite scroll pagination

### 5. Collaborative Editor

- User presence tracking
- Real-time cursor positions (broadcast)
- Document change subscriptions
- Conflict resolution
- Version history

## Best Practices

1. **Security First**
   - Always enable Row-Level Security (RLS)
   - Never expose service role key in client
   - Validate user input on both client and server
   - Use environment variables for credentials

2. **Performance**
   - Select only needed columns
   - Use pagination for large datasets
   - Add database indexes for frequent queries
   - Reuse client instance (singleton pattern)

3. **Type Safety**
   - Generate types from database schema
   - Use TypeScript for all Supabase operations
   - Leverage IDE autocomplete

4. **Error Handling**
   - Always check error responses
   - Use throwOnError() for promise rejection
   - Implement proper error boundaries

5. **Realtime**
   - Clean up subscriptions when components unmount
   - Use filters to reduce unnecessary events
   - Consider bandwidth for high-frequency updates

## Resources

### Official Documentation
- [Supabase Docs](https://supabase.com/docs)
- [JavaScript Client Reference](https://supabase.com/docs/reference/javascript)
- [Database Reference](https://supabase.com/docs/guides/database)
- [Auth Reference](https://supabase.com/docs/guides/auth)
- [Storage Reference](https://supabase.com/docs/guides/storage)
- [Realtime Reference](https://supabase.com/docs/guides/realtime)

### Community
- [GitHub Repository](https://github.com/supabase/supabase)
- [Discord Community](https://discord.supabase.com)
- [Blog](https://supabase.com/blog)
- [YouTube Channel](https://www.youtube.com/c/supabase)

### Tools
- [Supabase CLI](https://supabase.com/docs/guides/cli)
- [VS Code Extension](https://marketplace.visualstudio.com/items?itemName=supabase.supabase-vscode)
- [Database Schema Visualizer](https://supabase-schema.vercel.app/)

## Migration from Firebase

Supabase provides migration guides for Firebase users:

- PostgreSQL vs Firestore data modeling
- Auth migration (users, OAuth providers)
- Storage migration
- Realtime migration
- Cloud Functions → Edge Functions

See [Firebase to Supabase Migration Guide](https://supabase.com/docs/guides/migrations/firebase)

## Support

For detailed implementation guidance:
- Read SKILL.md for comprehensive coverage
- Check EXAMPLES.md for practical code samples
- Consult REFERENCE.md for complete API details

For issues and questions:
- [GitHub Issues](https://github.com/supabase/supabase/issues)
- [Discord Community](https://discord.supabase.com)
- [Stack Overflow](https://stackoverflow.com/questions/tagged/supabase)

---

**Version**: 1.0.0
**Last Updated**: October 2025
**Maintained By**: Claude Code Skills Team
