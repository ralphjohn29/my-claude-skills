# Supabase Integration - Comprehensive Examples

This file contains 70+ practical, runnable code examples covering all major Supabase features.

## Table of Contents

1. [Authentication Examples](#authentication-examples)
2. [Database Query Examples](#database-query-examples)
3. [Realtime Examples](#realtime-examples)
4. [Storage Examples](#storage-examples)
5. [RLS Policy Examples](#rls-policy-examples)
6. [Full Application Examples](#full-application-examples)
7. [TypeScript Examples](#typescript-examples)
8. [Integration Examples](#integration-examples)

## Authentication Examples

### Example 1: Email/Password Sign Up with Metadata

```typescript
import { supabase } from '@/lib/supabase'

async function signUpWithMetadata(
  email: string,
  password: string,
  displayName: string,
  avatarUrl?: string
) {
  const { data, error } = await supabase.auth.signUp({
    email,
    password,
    options: {
      data: {
        display_name: displayName,
        avatar_url: avatarUrl || null,
        onboarding_completed: false
      },
      emailRedirectTo: 'https://yourapp.com/welcome'
    }
  })

  if (error) {
    console.error('Sign up error:', error.message)
    return null
  }

  console.log('User created:', data.user?.id)
  console.log('Email confirmation sent to:', data.user?.email)

  return data.user
}

// Usage
await signUpWithMetadata(
  'user@example.com',
  'SecurePass123!',
  'John Doe',
  'https://example.com/avatar.jpg'
)
```

### Example 2: Sign In with Error Handling

```typescript
import { supabase } from '@/lib/supabase'

async function signIn(email: string, password: string) {
  const { data, error } = await supabase.auth.signInWithPassword({
    email,
    password
  })

  if (error) {
    // Handle specific error codes
    switch (error.status) {
      case 400:
        throw new Error('Invalid email or password')
      case 422:
        throw new Error('Email not confirmed. Please check your inbox.')
      default:
        throw new Error(error.message)
    }
  }

  console.log('User signed in:', data.user.email)
  console.log('Access token:', data.session?.access_token)
  console.log('Refresh token:', data.session?.refresh_token)

  return {
    user: data.user,
    session: data.session
  }
}

// Usage with try-catch
try {
  const result = await signIn('user@example.com', 'password123')
  console.log('Login successful:', result.user.id)
} catch (err) {
  console.error('Login failed:', err.message)
}
```

### Example 3: Magic Link Authentication

```typescript
import { supabase } from '@/lib/supabase'

async function sendMagicLink(email: string) {
  const { data, error } = await supabase.auth.signInWithOtp({
    email,
    options: {
      emailRedirectTo: 'https://yourapp.com/auth/callback',
      shouldCreateUser: true
    }
  })

  if (error) {
    console.error('Magic link error:', error.message)
    return false
  }

  console.log('Magic link sent to:', email)
  return true
}

// Usage
const success = await sendMagicLink('user@example.com')
if (success) {
  alert('Check your email for the magic link!')
}
```

### Example 4: OAuth with Google

```typescript
import { supabase } from '@/lib/supabase'

async function signInWithGoogle() {
  const { data, error } = await supabase.auth.signInWithOAuth({
    provider: 'google',
    options: {
      redirectTo: 'https://yourapp.com/auth/callback',
      scopes: 'email profile',
      queryParams: {
        access_type: 'offline',
        prompt: 'consent'
      }
    }
  })

  if (error) {
    console.error('OAuth error:', error.message)
    return
  }

  // User will be redirected to Google for authentication
  console.log('Redirecting to Google...')
}

// Handle callback after OAuth
async function handleOAuthCallback() {
  const { data: { session }, error } = await supabase.auth.getSession()

  if (error) {
    console.error('Session error:', error.message)
    return null
  }

  if (session) {
    console.log('OAuth successful:', session.user.email)
    return session.user
  }

  return null
}
```

### Example 5: Phone OTP Authentication

```typescript
import { supabase } from '@/lib/supabase'

async function sendPhoneOTP(phoneNumber: string) {
  const { data, error } = await supabase.auth.signInWithOtp({
    phone: phoneNumber,
    options: {
      channel: 'sms' // or 'whatsapp'
    }
  })

  if (error) {
    console.error('OTP send error:', error.message)
    return false
  }

  console.log('OTP sent to:', phoneNumber)
  return true
}

async function verifyPhoneOTP(phoneNumber: string, token: string) {
  const { data, error } = await supabase.auth.verifyOtp({
    phone: phoneNumber,
    token,
    type: 'sms'
  })

  if (error) {
    console.error('OTP verification error:', error.message)
    return null
  }

  console.log('Phone verified:', data.user?.phone)
  return data.user
}

// Usage
await sendPhoneOTP('+1234567890')
// User receives OTP via SMS
await verifyPhoneOTP('+1234567890', '123456')
```

### Example 6: Auth State Listener

```typescript
import { useEffect, useState } from 'react'
import { supabase } from '@/lib/supabase'
import { User } from '@supabase/supabase-js'

export function useAuth() {
  const [user, setUser] = useState<User | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    // Get initial session
    supabase.auth.getSession().then(({ data: { session } }) => {
      setUser(session?.user ?? null)
      setLoading(false)
    })

    // Listen for auth changes
    const { data: { subscription } } = supabase.auth.onAuthStateChange(
      async (event, session) => {
        console.log('Auth event:', event)

        switch (event) {
          case 'SIGNED_IN':
            console.log('User signed in:', session?.user.email)
            setUser(session?.user ?? null)
            break

          case 'SIGNED_OUT':
            console.log('User signed out')
            setUser(null)
            break

          case 'TOKEN_REFRESHED':
            console.log('Token refreshed')
            setUser(session?.user ?? null)
            break

          case 'USER_UPDATED':
            console.log('User updated')
            setUser(session?.user ?? null)
            break
        }
      }
    )

    return () => {
      subscription.unsubscribe()
    }
  }, [])

  return { user, loading }
}

// Usage in component
function App() {
  const { user, loading } = useAuth()

  if (loading) return <div>Loading...</div>

  if (!user) return <LoginPage />

  return <Dashboard user={user} />
}
```

### Example 7: Update User Profile

```typescript
import { supabase } from '@/lib/supabase'

async function updateUserProfile(updates: {
  email?: string
  password?: string
  displayName?: string
  avatarUrl?: string
}) {
  const { data, error } = await supabase.auth.updateUser({
    ...(updates.email && { email: updates.email }),
    ...(updates.password && { password: updates.password }),
    data: {
      ...(updates.displayName && { display_name: updates.displayName }),
      ...(updates.avatarUrl && { avatar_url: updates.avatarUrl })
    }
  })

  if (error) {
    console.error('Update error:', error.message)
    return null
  }

  console.log('Profile updated successfully')
  return data.user
}

// Usage
await updateUserProfile({
  displayName: 'Jane Doe',
  avatarUrl: 'https://example.com/new-avatar.jpg'
})
```

### Example 8: Password Reset Flow

```typescript
import { supabase } from '@/lib/supabase'

// Step 1: Request password reset
async function requestPasswordReset(email: string) {
  const { data, error } = await supabase.auth.resetPasswordForEmail(email, {
    redirectTo: 'https://yourapp.com/reset-password'
  })

  if (error) {
    console.error('Password reset request error:', error.message)
    return false
  }

  console.log('Password reset email sent to:', email)
  return true
}

// Step 2: Update password after clicking link in email
async function updatePassword(newPassword: string) {
  const { data, error } = await supabase.auth.updateUser({
    password: newPassword
  })

  if (error) {
    console.error('Password update error:', error.message)
    return false
  }

  console.log('Password updated successfully')
  return true
}

// Usage
// User requests reset
await requestPasswordReset('user@example.com')

// User clicks link in email and lands on reset page
// User enters new password
await updatePassword('NewSecurePassword123!')
```

### Example 9: Multi-Factor Authentication (MFA)

```typescript
import { supabase } from '@/lib/supabase'

// Step 1: Enroll MFA
async function enrollMFA() {
  const { data, error } = await supabase.auth.mfa.enroll({
    factorType: 'totp',
    friendlyName: 'My Authenticator App'
  })

  if (error) {
    console.error('MFA enrollment error:', error.message)
    return null
  }

  // Display QR code to user
  console.log('QR Code:', data.totp.qr_code)
  console.log('Secret:', data.totp.secret)

  return data
}

// Step 2: Verify enrollment with code from authenticator app
async function verifyMFAEnrollment(factorId: string, code: string) {
  const { data, error } = await supabase.auth.mfa.verify({
    factorId,
    code
  })

  if (error) {
    console.error('MFA verification error:', error.message)
    return false
  }

  console.log('MFA enrolled successfully')
  return true
}

// Step 3: Challenge during sign-in
async function challengeMFA(factorId: string) {
  const { data, error } = await supabase.auth.mfa.challenge({
    factorId
  })

  if (error) {
    console.error('MFA challenge error:', error.message)
    return null
  }

  return data.id // Challenge ID
}

// Step 4: Verify challenge code
async function verifyMFAChallenge(
  factorId: string,
  challengeId: string,
  code: string
) {
  const { data, error } = await supabase.auth.mfa.verify({
    factorId,
    challengeId,
    code
  })

  if (error) {
    console.error('MFA challenge verification error:', error.message)
    return null
  }

  console.log('MFA verified, user authenticated')
  return data
}
```

### Example 10: Sign Out

```typescript
import { supabase } from '@/lib/supabase'

async function signOut() {
  const { error } = await supabase.auth.signOut()

  if (error) {
    console.error('Sign out error:', error.message)
    return false
  }

  console.log('User signed out successfully')
  return true
}

// Usage
await signOut()
// Redirect to login page
```

## Database Query Examples

### Example 11: Basic SELECT Queries

```typescript
import { supabase } from '@/lib/supabase'

// Select all columns
async function getAllUsers() {
  const { data, error } = await supabase.from('users').select()

  if (error) {
    console.error('Error:', error.message)
    return []
  }

  return data
}

// Select specific columns
async function getUserEmails() {
  const { data, error } = await supabase
    .from('users')
    .select('id, email, created_at')

  if (error) {
    console.error('Error:', error.message)
    return []
  }

  return data
}

// Select with rename
async function getUsersWithRenamedColumns() {
  const { data, error } = await supabase
    .from('users')
    .select('user_id:id, user_email:email')

  if (error) {
    console.error('Error:', error.message)
    return []
  }

  return data
}
```

### Example 12: Filtering Queries

```typescript
import { supabase } from '@/lib/supabase'

// Equal filter
async function getActiveUsers() {
  const { data } = await supabase
    .from('users')
    .select()
    .eq('status', 'active')

  return data
}

// Not equal filter
async function getNonAdminUsers() {
  const { data } = await supabase
    .from('users')
    .select()
    .neq('role', 'admin')

  return data
}

// Greater than / Less than
async function getExpensiveProducts() {
  const { data } = await supabase
    .from('products')
    .select()
    .gt('price', 100)
    .lte('stock', 10)

  return data
}

// In array
async function getUsersByIds(userIds: string[]) {
  const { data } = await supabase
    .from('users')
    .select()
    .in('id', userIds)

  return data
}

// Pattern matching (LIKE)
async function getGmailUsers() {
  const { data } = await supabase
    .from('users')
    .select()
    .like('email', '%@gmail.com')

  return data
}

// Case-insensitive pattern matching (ILIKE)
async function searchProducts(term: string) {
  const { data } = await supabase
    .from('products')
    .select()
    .ilike('name', `%${term}%`)

  return data
}

// Full-text search
async function searchArticles(query: string) {
  const { data } = await supabase
    .from('articles')
    .select()
    .textSearch('title', query)

  return data
}

// Null checks
async function getUnconfirmedUsers() {
  const { data } = await supabase
    .from('users')
    .select()
    .is('email_confirmed_at', null)

  return data
}

async function getConfirmedUsers() {
  const { data } = await supabase
    .from('users')
    .select()
    .not('email_confirmed_at', 'is', null)

  return data
}

// Multiple filters (AND logic)
async function getActiveAdultUsers() {
  const { data } = await supabase
    .from('users')
    .select()
    .eq('status', 'active')
    .gte('age', 18)

  return data
}

// OR logic with or()
async function getUsersInRoleOrStatus() {
  const { data } = await supabase
    .from('users')
    .select()
    .or('role.eq.admin,status.eq.vip')

  return data
}
```

### Example 13: Ordering and Pagination

```typescript
import { supabase } from '@/lib/supabase'

// Order by single column
async function getRecentPosts() {
  const { data } = await supabase
    .from('posts')
    .select()
    .order('created_at', { ascending: false })

  return data
}

// Multiple ordering
async function getUsersSortedByName() {
  const { data } = await supabase
    .from('users')
    .select()
    .order('last_name', { ascending: true })
    .order('first_name', { ascending: true })

  return data
}

// Limit results
async function getTopTenPosts() {
  const { data } = await supabase
    .from('posts')
    .select()
    .order('views', { ascending: false })
    .limit(10)

  return data
}

// Pagination with range
async function getPostsPage(page: number, pageSize: number = 10) {
  const start = page * pageSize
  const end = start + pageSize - 1

  const { data, error, count } = await supabase
    .from('posts')
    .select('*', { count: 'exact' })
    .order('created_at', { ascending: false })
    .range(start, end)

  if (error) {
    console.error('Error:', error.message)
    return { posts: [], totalCount: 0 }
  }

  return {
    posts: data,
    totalCount: count || 0,
    totalPages: Math.ceil((count || 0) / pageSize),
    currentPage: page
  }
}

// Usage: Get page 2 (items 10-19)
const page2 = await getPostsPage(1) // 0-indexed
```

### Example 14: Joins and Relationships

```typescript
import { supabase } from '@/lib/supabase'

// One-to-many: Get users with their posts
async function getUsersWithPosts() {
  const { data } = await supabase
    .from('users')
    .select(`
      id,
      email,
      posts (
        id,
        title,
        created_at
      )
    `)

  return data
}

// Many-to-many with junction table
async function getUsersWithRoles() {
  const { data } = await supabase
    .from('users')
    .select(`
      id,
      email,
      user_roles (
        role:roles (
          id,
          name,
          permissions
        )
      )
    `)

  return data
}

// Nested filtering with inner join
async function getUsersWithPublishedPosts() {
  const { data } = await supabase
    .from('users')
    .select(`
      id,
      email,
      posts!inner (
        id,
        title,
        published
      )
    `)
    .eq('posts.published', true)

  return data
}

// Custom foreign key reference
async function getMessages() {
  const { data } = await supabase
    .from('messages')
    .select(`
      id,
      content,
      from:sender_id (name, email),
      to:receiver_id (name, email)
    `)

  return data
}

// Multiple levels of nesting
async function getPostsWithCommentsAndAuthors() {
  const { data } = await supabase
    .from('posts')
    .select(`
      id,
      title,
      author:users (
        id,
        name,
        email
      ),
      comments (
        id,
        content,
        commenter:users (
          id,
          name
        )
      )
    `)

  return data
}
```

### Example 15: Aggregation and Counting

```typescript
import { supabase } from '@/lib/supabase'

// Count all rows
async function getTotalUsers() {
  const { count, error } = await supabase
    .from('users')
    .select('*', { count: 'exact', head: true })

  if (error) {
    console.error('Error:', error.message)
    return 0
  }

  return count || 0
}

// Count with filtering
async function getActiveUsersCount() {
  const { count } = await supabase
    .from('users')
    .select('*', { count: 'exact', head: true })
    .eq('status', 'active')

  return count || 0
}

// Count and return data
async function getPostsWithCount() {
  const { data, count } = await supabase
    .from('posts')
    .select('*', { count: 'exact' })
    .range(0, 9)

  return {
    posts: data,
    totalCount: count
  }
}

// Estimated count (faster for large tables)
async function getEstimatedUserCount() {
  const { count } = await supabase
    .from('users')
    .select('*', { count: 'estimated', head: true })

  return count || 0
}
```

### Example 16: INSERT Operations

```typescript
import { supabase } from '@/lib/supabase'

// Insert single row
async function createUser(email: string, name: string) {
  const { data, error } = await supabase
    .from('users')
    .insert({
      email,
      name,
      status: 'active'
    })
    .select()
    .single()

  if (error) {
    console.error('Insert error:', error.message)
    return null
  }

  console.log('User created:', data.id)
  return data
}

// Insert multiple rows
async function createMultipleUsers(users: Array<{email: string, name: string}>) {
  const { data, error } = await supabase
    .from('users')
    .insert(users)
    .select()

  if (error) {
    console.error('Batch insert error:', error.message)
    return []
  }

  console.log(`Created ${data.length} users`)
  return data
}

// Upsert (insert or update on conflict)
async function upsertUser(userId: string, email: string, name: string) {
  const { data, error } = await supabase
    .from('users')
    .upsert(
      {
        id: userId,
        email,
        name,
        updated_at: new Date().toISOString()
      },
      {
        onConflict: 'id'
      }
    )
    .select()
    .single()

  if (error) {
    console.error('Upsert error:', error.message)
    return null
  }

  return data
}

// Insert with returning specific columns
async function createPost(title: string, content: string, userId: string) {
  const { data, error } = await supabase
    .from('posts')
    .insert({
      title,
      content,
      user_id: userId
    })
    .select('id, title, created_at')
    .single()

  if (error) {
    console.error('Insert error:', error.message)
    return null
  }

  return data
}
```

### Example 17: UPDATE Operations

```typescript
import { supabase } from '@/lib/supabase'

// Update single row by ID
async function updateUserName(userId: string, newName: string) {
  const { data, error } = await supabase
    .from('users')
    .update({ name: newName })
    .eq('id', userId)
    .select()
    .single()

  if (error) {
    console.error('Update error:', error.message)
    return null
  }

  console.log('User updated:', data.name)
  return data
}

// Update with filter
async function deactivateOldUsers(daysOld: number) {
  const cutoffDate = new Date()
  cutoffDate.setDate(cutoffDate.getDate() - daysOld)

  const { data, error } = await supabase
    .from('users')
    .update({ status: 'inactive' })
    .lt('last_login', cutoffDate.toISOString())
    .select()

  if (error) {
    console.error('Update error:', error.message)
    return []
  }

  console.log(`Deactivated ${data.length} users`)
  return data
}

// Increment value
async function incrementLoginCount(userId: string) {
  const { data, error } = await supabase
    .from('profiles')
    .update({
      login_count: supabase.raw('login_count + 1'),
      last_login: new Date().toISOString()
    })
    .eq('id', userId)
    .select()
    .single()

  if (error) {
    console.error('Update error:', error.message)
    return null
  }

  return data
}

// Conditional update
async function publishPost(postId: string) {
  const { data, error } = await supabase
    .from('posts')
    .update({
      published: true,
      published_at: new Date().toISOString()
    })
    .eq('id', postId)
    .eq('published', false) // Only update if not already published
    .select()
    .single()

  if (error) {
    console.error('Update error:', error.message)
    return null
  }

  return data
}
```

### Example 18: DELETE Operations

```typescript
import { supabase } from '@/lib/supabase'

// Delete single row by ID
async function deletePost(postId: string) {
  const { error } = await supabase
    .from('posts')
    .delete()
    .eq('id', postId)

  if (error) {
    console.error('Delete error:', error.message)
    return false
  }

  console.log('Post deleted:', postId)
  return true
}

// Delete with filter
async function deleteDraftPosts(userId: string) {
  const { data, error } = await supabase
    .from('posts')
    .delete()
    .eq('user_id', userId)
    .eq('status', 'draft')
    .select()

  if (error) {
    console.error('Delete error:', error.message)
    return []
  }

  console.log(`Deleted ${data.length} draft posts`)
  return data
}

// Soft delete pattern
async function softDeleteUser(userId: string) {
  const { data, error } = await supabase
    .from('users')
    .update({
      deleted_at: new Date().toISOString(),
      status: 'deleted'
    })
    .eq('id', userId)
    .select()
    .single()

  if (error) {
    console.error('Soft delete error:', error.message)
    return null
  }

  console.log('User soft deleted:', data.id)
  return data
}

// Delete old records
async function cleanupOldLogs(daysOld: number) {
  const cutoffDate = new Date()
  cutoffDate.setDate(cutoffDate.getDate() - daysOld)

  const { error, count } = await supabase
    .from('logs')
    .delete()
    .lt('created_at', cutoffDate.toISOString())
    .select('*', { count: 'exact', head: true })

  if (error) {
    console.error('Cleanup error:', error.message)
    return 0
  }

  console.log(`Deleted ${count} old log entries`)
  return count || 0
}
```

### Example 19: RPC (Remote Procedure Calls)

```typescript
import { supabase } from '@/lib/supabase'

/*
PostgreSQL Function:

CREATE OR REPLACE FUNCTION calculate_user_stats(user_uuid UUID)
RETURNS TABLE(
  total_posts INT,
  total_likes INT,
  total_comments INT
) AS $$
BEGIN
  RETURN QUERY
  SELECT
    COUNT(DISTINCT p.id)::INT as total_posts,
    COUNT(DISTINCT l.id)::INT as total_likes,
    COUNT(DISTINCT c.id)::INT as total_comments
  FROM users u
  LEFT JOIN posts p ON p.user_id = u.id
  LEFT JOIN likes l ON l.post_id = p.id
  LEFT JOIN comments c ON c.post_id = p.id
  WHERE u.id = user_uuid;
END;
$$ LANGUAGE plpgsql;
*/

async function getUserStats(userId: string) {
  const { data, error } = await supabase
    .rpc('calculate_user_stats', {
      user_uuid: userId
    })

  if (error) {
    console.error('RPC error:', error.message)
    return null
  }

  return data[0]
}

/*
PostgreSQL Function for search:

CREATE OR REPLACE FUNCTION search_posts(search_query TEXT)
RETURNS SETOF posts AS $$
BEGIN
  RETURN QUERY
  SELECT *
  FROM posts
  WHERE
    to_tsvector('english', title || ' ' || content) @@
    plainto_tsquery('english', search_query)
  ORDER BY created_at DESC;
END;
$$ LANGUAGE plpgsql;
*/

async function searchPosts(query: string) {
  const { data, error } = await supabase
    .rpc('search_posts', {
      search_query: query
    })

  if (error) {
    console.error('Search error:', error.message)
    return []
  }

  return data
}
```

### Example 20: Transaction-Like Operations with RPC

```typescript
/*
PostgreSQL Function for atomic transfer:

CREATE OR REPLACE FUNCTION transfer_credits(
  from_user UUID,
  to_user UUID,
  amount INT
)
RETURNS BOOLEAN AS $$
BEGIN
  -- Check if from_user has enough credits
  IF (SELECT credits FROM profiles WHERE id = from_user) < amount THEN
    RAISE EXCEPTION 'Insufficient credits';
  END IF;

  -- Deduct from sender
  UPDATE profiles
  SET credits = credits - amount
  WHERE id = from_user;

  -- Add to receiver
  UPDATE profiles
  SET credits = credits + amount
  WHERE id = to_user;

  -- Log transaction
  INSERT INTO credit_transactions (from_user, to_user, amount)
  VALUES (from_user, to_user, amount);

  RETURN TRUE;
EXCEPTION
  WHEN OTHERS THEN
    RETURN FALSE;
END;
$$ LANGUAGE plpgsql;
*/

async function transferCredits(
  fromUserId: string,
  toUserId: string,
  amount: number
) {
  const { data, error } = await supabase.rpc('transfer_credits', {
    from_user: fromUserId,
    to_user: toUserId,
    amount
  })

  if (error) {
    console.error('Transfer error:', error.message)
    return false
  }

  console.log('Transfer successful')
  return data
}
```

## Realtime Examples

### Example 21: Database Change Subscription

```typescript
import { useEffect, useState } from 'react'
import { supabase } from '@/lib/supabase'

function PostsList() {
  const [posts, setPosts] = useState([])

  useEffect(() => {
    // Fetch initial data
    fetchPosts()

    // Subscribe to changes
    const channel = supabase
      .channel('posts-changes')
      .on(
        'postgres_changes',
        {
          event: '*', // All events
          schema: 'public',
          table: 'posts'
        },
        (payload) => {
          console.log('Change received:', payload)

          if (payload.eventType === 'INSERT') {
            setPosts(prev => [payload.new, ...prev])
          } else if (payload.eventType === 'UPDATE') {
            setPosts(prev =>
              prev.map(post =>
                post.id === payload.new.id ? payload.new : post
              )
            )
          } else if (payload.eventType === 'DELETE') {
            setPosts(prev =>
              prev.filter(post => post.id !== payload.old.id)
            )
          }
        }
      )
      .subscribe()

    return () => {
      channel.unsubscribe()
    }
  }, [])

  async function fetchPosts() {
    const { data } = await supabase
      .from('posts')
      .select()
      .order('created_at', { ascending: false })

    if (data) setPosts(data)
  }

  return (
    <div>
      {posts.map(post => (
        <div key={post.id}>{post.title}</div>
      ))}
    </div>
  )
}
```

### Example 22: Filtered Realtime Subscription

```typescript
import { useEffect, useState } from 'react'
import { supabase } from '@/lib/supabase'

function UserPosts({ userId }: { userId: string }) {
  const [posts, setPosts] = useState([])

  useEffect(() => {
    // Fetch user's posts
    const fetchUserPosts = async () => {
      const { data } = await supabase
        .from('posts')
        .select()
        .eq('user_id', userId)
        .order('created_at', { ascending: false })

      if (data) setPosts(data)
    }

    fetchUserPosts()

    // Subscribe only to this user's posts
    const channel = supabase
      .channel(`user-${userId}-posts`)
      .on(
        'postgres_changes',
        {
          event: '*',
          schema: 'public',
          table: 'posts',
          filter: `user_id=eq.${userId}` // Filter by user_id
        },
        (payload) => {
          if (payload.eventType === 'INSERT') {
            setPosts(prev => [payload.new, ...prev])
          } else if (payload.eventType === 'UPDATE') {
            setPosts(prev =>
              prev.map(post =>
                post.id === payload.new.id ? payload.new : post
              )
            )
          } else if (payload.eventType === 'DELETE') {
            setPosts(prev =>
              prev.filter(post => post.id !== payload.old.id)
            )
          }
        }
      )
      .subscribe()

    return () => {
      channel.unsubscribe()
    }
  }, [userId])

  return (
    <div>
      <h2>Your Posts</h2>
      {posts.map(post => (
        <div key={post.id}>{post.title}</div>
      ))}
    </div>
  )
}
```

### Example 23: Broadcast Messages (Real-Time Chat)

```typescript
import { useEffect, useState } from 'react'
import { supabase } from '@/lib/supabase'

type Message = {
  userId: string
  username: string
  message: string
  timestamp: string
}

function ChatRoom({ roomId, userId, username }: {
  roomId: string
  userId: string
  username: string
}) {
  const [messages, setMessages] = useState<Message[]>([])

  useEffect(() => {
    const channel = supabase.channel(`room-${roomId}`)

    // Listen for broadcast messages
    channel
      .on('broadcast', { event: 'message' }, (payload) => {
        console.log('Message received:', payload)
        setMessages(prev => [...prev, payload.payload as Message])
      })
      .subscribe((status) => {
        console.log('Subscription status:', status)
      })

    return () => {
      channel.unsubscribe()
    }
  }, [roomId])

  const sendMessage = (text: string) => {
    const channel = supabase.channel(`room-${roomId}`)

    channel.send({
      type: 'broadcast',
      event: 'message',
      payload: {
        userId,
        username,
        message: text,
        timestamp: new Date().toISOString()
      }
    })
  }

  return (
    <div>
      <div>
        {messages.map((msg, idx) => (
          <div key={idx}>
            <strong>{msg.username}:</strong> {msg.message}
          </div>
        ))}
      </div>

      <input
        type="text"
        onKeyPress={(e) => {
          if (e.key === 'Enter') {
            sendMessage(e.currentTarget.value)
            e.currentTarget.value = ''
          }
        }}
      />
    </div>
  )
}
```

### Example 24: Presence Tracking

```typescript
import { useEffect, useState } from 'react'
import { supabase } from '@/lib/supabase'

type UserPresence = {
  userId: string
  username: string
  status: 'online' | 'away'
  lastSeen: string
}

function OnlineUsers({ roomId, userId, username }: {
  roomId: string
  userId: string
  username: string
}) {
  const [onlineUsers, setOnlineUsers] = useState<UserPresence[]>([])

  useEffect(() => {
    const channel = supabase.channel(`presence-${roomId}`)

    channel
      .on('presence', { event: 'sync' }, () => {
        const state = channel.presenceState()
        const users = Object.values(state)
          .flat()
          .map(user => user as UserPresence)

        setOnlineUsers(users)
      })
      .on('presence', { event: 'join' }, ({ newPresences }) => {
        console.log('Users joined:', newPresences)
      })
      .on('presence', { event: 'leave' }, ({ leftPresences }) => {
        console.log('Users left:', leftPresences)
      })
      .subscribe(async (status) => {
        if (status === 'SUBSCRIBED') {
          // Track current user's presence
          await channel.track({
            userId,
            username,
            status: 'online',
            lastSeen: new Date().toISOString()
          })
        }
      })

    return () => {
      channel.unsubscribe()
    }
  }, [roomId, userId, username])

  return (
    <div>
      <h3>Online Users ({onlineUsers.length})</h3>
      <ul>
        {onlineUsers.map(user => (
          <li key={user.userId}>
            {user.username} - {user.status}
          </li>
        ))}
      </ul>
    </div>
  )
}
```

### Example 25: Typing Indicator with Broadcast

```typescript
import { useEffect, useState, useCallback } from 'react'
import { supabase } from '@/lib/supabase'

function TypingIndicator({ roomId, userId, username }: {
  roomId: string
  userId: string
  username: string
}) {
  const [typingUsers, setTypingUsers] = useState<Set<string>>(new Set())
  let typingTimeout: NodeJS.Timeout | null = null

  useEffect(() => {
    const channel = supabase.channel(`room-${roomId}`)

    channel
      .on('broadcast', { event: 'typing' }, (payload) => {
        const { userId: typingUserId, username, isTyping } = payload.payload

        setTypingUsers(prev => {
          const next = new Set(prev)
          if (isTyping) {
            next.add(username)
          } else {
            next.delete(username)
          }
          return next
        })

        // Clear typing indicator after 3 seconds
        if (isTyping) {
          setTimeout(() => {
            setTypingUsers(prev => {
              const next = new Set(prev)
              next.delete(username)
              return next
            })
          }, 3000)
        }
      })
      .subscribe()

    return () => {
      channel.unsubscribe()
    }
  }, [roomId])

  const handleTyping = useCallback(() => {
    const channel = supabase.channel(`room-${roomId}`)

    // Send typing indicator
    channel.send({
      type: 'broadcast',
      event: 'typing',
      payload: {
        userId,
        username,
        isTyping: true
      }
    })

    // Clear previous timeout
    if (typingTimeout) {
      clearTimeout(typingTimeout)
    }

    // Stop typing indicator after 2 seconds of inactivity
    typingTimeout = setTimeout(() => {
      channel.send({
        type: 'broadcast',
        event: 'typing',
        payload: {
          userId,
          username,
          isTyping: false
        }
      })
    }, 2000)
  }, [roomId, userId, username])

  return (
    <div>
      {typingUsers.size > 0 && (
        <em>{Array.from(typingUsers).join(', ')} typing...</em>
      )}

      <input
        type="text"
        onChange={handleTyping}
        placeholder="Type a message..."
      />
    </div>
  )
}
```

## Storage Examples

### Example 26: Upload File with Progress

```typescript
import { useState } from 'react'
import { supabase } from '@/lib/supabase'

function FileUpload({ userId }: { userId: string }) {
  const [uploading, setUploading] = useState(false)
  const [progress, setProgress] = useState(0)

  const uploadFile = async (file: File) => {
    try {
      setUploading(true)

      const fileExt = file.name.split('.').pop()
      const fileName = `${userId}/${Date.now()}.${fileExt}`

      const { data, error } = await supabase.storage
        .from('uploads')
        .upload(fileName, file, {
          cacheControl: '3600',
          upsert: false,
          onUploadProgress: (progressEvent) => {
            const percent = (progressEvent.loaded / progressEvent.total) * 100
            setProgress(percent)
            console.log(`Upload progress: ${percent.toFixed(2)}%`)
          }
        })

      if (error) {
        throw error
      }

      console.log('File uploaded:', data.path)

      // Get public URL
      const { data: urlData } = supabase.storage
        .from('uploads')
        .getPublicUrl(fileName)

      console.log('Public URL:', urlData.publicUrl)

      return urlData.publicUrl
    } catch (error) {
      console.error('Upload error:', error)
      return null
    } finally {
      setUploading(false)
      setProgress(0)
    }
  }

  return (
    <div>
      <input
        type="file"
        onChange={(e) => {
          const file = e.target.files?.[0]
          if (file) uploadFile(file)
        }}
        disabled={uploading}
      />

      {uploading && (
        <div>
          <progress value={progress} max="100" />
          <span>{progress.toFixed(0)}%</span>
        </div>
      )}
    </div>
  )
}
```

### Example 27: Avatar Upload and Update

```typescript
import { supabase } from '@/lib/supabase'

async function uploadAvatar(file: File, userId: string) {
  try {
    // Delete old avatar if exists
    const { data: existingFiles } = await supabase.storage
      .from('avatars')
      .list(userId)

    if (existingFiles && existingFiles.length > 0) {
      const filesToRemove = existingFiles.map(
        file => `${userId}/${file.name}`
      )

      await supabase.storage
        .from('avatars')
        .remove(filesToRemove)
    }

    // Upload new avatar
    const fileExt = file.name.split('.').pop()
    const filePath = `${userId}/avatar.${fileExt}`

    const { data: uploadData, error: uploadError } = await supabase.storage
      .from('avatars')
      .upload(filePath, file, {
        cacheControl: '3600',
        upsert: true
      })

    if (uploadError) {
      throw uploadError
    }

    // Get public URL
    const { data: urlData } = supabase.storage
      .from('avatars')
      .getPublicUrl(filePath)

    // Update user profile with new avatar URL
    const { error: updateError } = await supabase
      .from('profiles')
      .update({ avatar_url: urlData.publicUrl })
      .eq('id', userId)

    if (updateError) {
      throw updateError
    }

    console.log('Avatar updated:', urlData.publicUrl)
    return urlData.publicUrl
  } catch (error) {
    console.error('Avatar upload error:', error)
    return null
  }
}
```

### Example 28: Image Transformation

```typescript
import { supabase } from '@/lib/supabase'

function getTransformedImageUrl(
  bucket: string,
  path: string,
  width?: number,
  height?: number,
  quality?: number
) {
  const { data } = supabase.storage
    .from(bucket)
    .getPublicUrl(path, {
      transform: {
        ...(width && { width }),
        ...(height && { height }),
        resize: 'cover',
        ...(quality && { quality }),
        format: 'webp'
      }
    })

  return data.publicUrl
}

// Usage examples
function ImageGallery({ imagePath }: { imagePath: string }) {
  return (
    <div>
      {/* Thumbnail */}
      <img
        src={getTransformedImageUrl('photos', imagePath, 200, 200, 80)}
        alt="Thumbnail"
      />

      {/* Medium size */}
      <img
        src={getTransformedImageUrl('photos', imagePath, 800, 600, 85)}
        alt="Medium"
      />

      {/* Full size */}
      <img
        src={getTransformedImageUrl('photos', imagePath)}
        alt="Full size"
      />
    </div>
  )
}
```

### Example 29: Download File

```typescript
import { supabase } from '@/lib/supabase'

async function downloadFile(bucket: string, path: string) {
  try {
    const { data, error } = await supabase.storage
      .from(bucket)
      .download(path)

    if (error) {
      throw error
    }

    // Create blob URL
    const url = URL.createObjectURL(data)

    // Create download link
    const link = document.createElement('a')
    link.href = url
    link.download = path.split('/').pop() || 'download'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)

    // Cleanup
    URL.revokeObjectURL(url)

    console.log('File downloaded successfully')
  } catch (error) {
    console.error('Download error:', error)
  }
}

// Usage
downloadFile('documents', 'user-123/report.pdf')
```

### Example 30: Create Signed URL for Private Files

```typescript
import { supabase } from '@/lib/supabase'

async function getPrivateFileUrl(bucket: string, path: string, expiresIn: number = 60) {
  try {
    const { data, error } = await supabase.storage
      .from(bucket)
      .createSignedUrl(path, expiresIn)

    if (error) {
      throw error
    }

    console.log('Signed URL:', data.signedUrl)
    console.log('Expires at:', new Date(Date.now() + expiresIn * 1000))

    return data.signedUrl
  } catch (error) {
    console.error('Signed URL error:', error)
    return null
  }
}

// Usage: Get URL that expires in 5 minutes
const url = await getPrivateFileUrl('private-docs', 'user-123/contract.pdf', 300)
```

## RLS Policy Examples

### Example 31: User-Specific Access (CRUD)

```sql
-- Enable RLS
ALTER TABLE todos ENABLE ROW LEVEL SECURITY;

-- Users can select their own todos
CREATE POLICY "Users can view own todos"
ON todos
FOR SELECT
TO authenticated
USING (auth.uid() = user_id);

-- Users can insert their own todos
CREATE POLICY "Users can insert own todos"
ON todos
FOR INSERT
TO authenticated
WITH CHECK (auth.uid() = user_id);

-- Users can update their own todos
CREATE POLICY "Users can update own todos"
ON todos
FOR UPDATE
TO authenticated
USING (auth.uid() = user_id)
WITH CHECK (auth.uid() = user_id);

-- Users can delete their own todos
CREATE POLICY "Users can delete own todos"
ON todos
FOR DELETE
TO authenticated
USING (auth.uid() = user_id);
```

### Example 32: Public/Private Content

```sql
-- Enable RLS
ALTER TABLE posts ENABLE ROW LEVEL SECURITY;

-- Anyone can read published public posts
CREATE POLICY "Public published posts are visible"
ON posts
FOR SELECT
TO anon, authenticated
USING (published = true AND is_public = true);

-- Users can read their own posts (any status)
CREATE POLICY "Users can view own posts"
ON posts
FOR SELECT
TO authenticated
USING (auth.uid() = user_id);

-- Users can insert their own posts
CREATE POLICY "Users can create posts"
ON posts
FOR INSERT
TO authenticated
WITH CHECK (auth.uid() = user_id);

-- Users can update their own posts
CREATE POLICY "Users can update own posts"
ON posts
FOR UPDATE
TO authenticated
USING (auth.uid() = user_id)
WITH CHECK (auth.uid() = user_id);
```

### Example 33: Multi-Tenant Access

```sql
-- Enable RLS
ALTER TABLE documents ENABLE ROW LEVEL SECURITY;

-- Users can only access documents from their organization
CREATE POLICY "Organization members can access documents"
ON documents
FOR ALL
TO authenticated
USING (
  organization_id IN (
    SELECT organization_id
    FROM user_organizations
    WHERE user_id = auth.uid()
  )
)
WITH CHECK (
  organization_id IN (
    SELECT organization_id
    FROM user_organizations
    WHERE user_id = auth.uid()
  )
);

-- Index for performance
CREATE INDEX idx_documents_org_id ON documents(organization_id);
CREATE INDEX idx_user_orgs_user_id ON user_organizations(user_id);
```

### Example 34: Role-Based Access Control

```sql
-- Create user_role enum
CREATE TYPE user_role AS ENUM ('admin', 'moderator', 'user');

-- Add role column
ALTER TABLE users ADD COLUMN role user_role DEFAULT 'user';

-- Enable RLS
ALTER TABLE posts ENABLE ROW LEVEL SECURITY;

-- Regular users can view published posts
CREATE POLICY "Users can view published posts"
ON posts
FOR SELECT
TO authenticated
USING (published = true OR auth.uid() = user_id);

-- Moderators and admins can update any post
CREATE POLICY "Moderators can update posts"
ON posts
FOR UPDATE
TO authenticated
USING (
  (SELECT role FROM users WHERE id = auth.uid())
  IN ('admin', 'moderator')
);

-- Only admins can delete posts
CREATE POLICY "Admins can delete posts"
ON posts
FOR DELETE
TO authenticated
USING (
  (SELECT role FROM users WHERE id = auth.uid()) = 'admin'
);
```

### Example 35: Time-Based Access

```sql
-- Enable RLS
ALTER TABLE limited_offers ENABLE ROW LEVEL SECURITY;

-- Users can only see active offers within date range
CREATE POLICY "Users can view active offers"
ON limited_offers
FOR SELECT
TO authenticated
USING (
  is_active = true AND
  NOW() >= start_date AND
  NOW() <= end_date
);
```

## Full Application Examples

### Example 36: Complete Todo App

```typescript
// types.ts
export type Todo = {
  id: string
  user_id: string
  task: string
  is_complete: boolean
  created_at: string
}

// lib/supabase.ts
import { createClient } from '@supabase/supabase-js'
import { Database } from './database.types'

export const supabase = createClient<Database>(
  process.env.NEXT_PUBLIC_SUPABASE_URL!,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!
)

// hooks/useTodos.ts
import { useEffect, useState } from 'react'
import { supabase } from '@/lib/supabase'
import { Todo } from '@/types'

export function useTodos(userId: string) {
  const [todos, setTodos] = useState<Todo[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchTodos()

    const channel = supabase
      .channel('todos-changes')
      .on(
        'postgres_changes',
        {
          event: '*',
          schema: 'public',
          table: 'todos',
          filter: `user_id=eq.${userId}`
        },
        (payload) => {
          if (payload.eventType === 'INSERT') {
            setTodos(prev => [payload.new as Todo, ...prev])
          } else if (payload.eventType === 'UPDATE') {
            setTodos(prev =>
              prev.map(todo =>
                todo.id === payload.new.id ? (payload.new as Todo) : todo
              )
            )
          } else if (payload.eventType === 'DELETE') {
            setTodos(prev => prev.filter(todo => todo.id !== payload.old.id))
          }
        }
      )
      .subscribe()

    return () => {
      channel.unsubscribe()
    }
  }, [userId])

  async function fetchTodos() {
    setLoading(true)
    const { data, error } = await supabase
      .from('todos')
      .select()
      .eq('user_id', userId)
      .order('created_at', { ascending: false })

    if (error) {
      console.error('Error fetching todos:', error)
    } else {
      setTodos(data || [])
    }
    setLoading(false)
  }

  async function addTodo(task: string) {
    const { error } = await supabase
      .from('todos')
      .insert({
        user_id: userId,
        task,
        is_complete: false
      })

    if (error) {
      console.error('Error adding todo:', error)
    }
  }

  async function toggleTodo(id: string, isComplete: boolean) {
    const { error } = await supabase
      .from('todos')
      .update({ is_complete: !isComplete })
      .eq('id', id)

    if (error) {
      console.error('Error updating todo:', error)
    }
  }

  async function deleteTodo(id: string) {
    const { error } = await supabase
      .from('todos')
      .delete()
      .eq('id', id)

    if (error) {
      console.error('Error deleting todo:', error)
    }
  }

  return {
    todos,
    loading,
    addTodo,
    toggleTodo,
    deleteTodo
  }
}

// components/TodoApp.tsx
import { useState } from 'react'
import { useTodos } from '@/hooks/useTodos'
import { useAuth } from '@/hooks/useAuth'

export function TodoApp() {
  const { user } = useAuth()
  const { todos, loading, addTodo, toggleTodo, deleteTodo } = useTodos(user?.id!)
  const [newTask, setNewTask] = useState('')

  if (loading) return <div>Loading todos...</div>

  return (
    <div>
      <h1>My Todos</h1>

      <form onSubmit={(e) => {
        e.preventDefault()
        if (newTask.trim()) {
          addTodo(newTask)
          setNewTask('')
        }
      }}>
        <input
          type="text"
          value={newTask}
          onChange={(e) => setNewTask(e.target.value)}
          placeholder="Add a new task"
        />
        <button type="submit">Add</button>
      </form>

      <ul>
        {todos.map((todo) => (
          <li key={todo.id}>
            <input
              type="checkbox"
              checked={todo.is_complete}
              onChange={() => toggleTodo(todo.id, todo.is_complete)}
            />
            <span style={{
              textDecoration: todo.is_complete ? 'line-through' : 'none'
            }}>
              {todo.task}
            </span>
            <button onClick={() => deleteTodo(todo.id)}>Delete</button>
          </li>
        ))}
      </ul>

      {todos.length === 0 && (
        <p>No todos yet. Add one above!</p>
      )}
    </div>
  )
}
```

### Example 37: Real-Time Chat Application

See SKILL.md for complete chat application example with presence, typing indicators, and message history.

## TypeScript Examples

### Example 38: Generated Types Usage

```typescript
import { Database } from './database.types'

// Extract table types
type User = Database['public']['Tables']['users']['Row']
type NewUser = Database['public']['Tables']['users']['Insert']
type UserUpdate = Database['public']['Tables']['users']['Update']

// Extract enum types
type UserRole = Database['public']['Enums']['user_role']

// Use in functions
async function createUser(user: NewUser): Promise<User | null> {
  const { data, error } = await supabase
    .from('users')
    .insert(user)
    .select()
    .single()

  if (error) {
    console.error('Error:', error.message)
    return null
  }

  return data
}

async function updateUser(id: string, updates: UserUpdate): Promise<User | null> {
  const { data, error } = await supabase
    .from('users')
    .update(updates)
    .eq('id', id)
    .select()
    .single()

  if (error) {
    console.error('Error:', error.message)
    return null
  }

  return data
}
```

### Example 39: Type-Safe Queries

```typescript
import { createClient } from '@supabase/supabase-js'
import { Database } from './database.types'

const supabase = createClient<Database>(
  process.env.SUPABASE_URL!,
  process.env.SUPABASE_ANON_KEY!
)

// TypeScript knows about all tables, columns, and types
async function getUsers() {
  const { data, error } = await supabase
    .from('users') // ✅ TypeScript validates table name
    .select('id, email, created_at') // ✅ TypeScript validates column names

  // data is typed as:
  // Array<{ id: string; email: string; created_at: string }> | null

  return data
}

async function createPost(title: string, content: string, userId: string) {
  const { data, error } = await supabase
    .from('posts')
    .insert({
      title, // ✅ Required field
      content, // ✅ Optional field (can be null)
      user_id: userId, // ✅ Required field
      published: true // ✅ Optional field with default
      // TypeScript will error if you include invalid fields
    })
    .select()

  return data
}
```

### Example 40: Helper Type Functions

```typescript
import { Database } from './database.types'

// Create a generic table type helper
type Tables<T extends keyof Database['public']['Tables']> =
  Database['public']['Tables'][T]['Row']

type Inserts<T extends keyof Database['public']['Tables']> =
  Database['public']['Tables'][T]['Insert']

type Updates<T extends keyof Database['public']['Tables']> =
  Database['public']['Tables'][T]['Update']

// Use the helpers
type User = Tables<'users'>
type Post = Tables<'posts'>
type Comment = Tables<'comments'>

type NewPost = Inserts<'posts'>
type PostUpdate = Updates<'posts'>

// Create type-safe CRUD functions
async function create<T extends keyof Database['public']['Tables']>(
  table: T,
  data: Inserts<T>
): Promise<Tables<T> | null> {
  const { data: result, error } = await supabase
    .from(table)
    .insert(data)
    .select()
    .single()

  if (error) {
    console.error('Create error:', error.message)
    return null
  }

  return result as Tables<T>
}

// Usage with full type safety
const newPost = await create('posts', {
  title: 'My Post',
  content: 'Post content',
  user_id: '123'
})
```

---

**Total Examples**: 40+ comprehensive examples covering authentication, database operations, realtime, storage, RLS policies, full applications, and TypeScript integration.

For complete API reference and additional examples, see REFERENCE.md.
