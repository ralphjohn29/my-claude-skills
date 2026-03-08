# UI/UX QA Audit Report Template

Use this template to generate comprehensive QA reports. Copy and fill in the sections.

---

# UI/UX QA Audit Report

**Report ID:** `[REPORT_ID]`
**Generated:** `[DATE_TIME]`
**Auditor:** Frontend UX QA Pro

---

## 📋 Executive Summary

| Metric | Value |
|--------|-------|
| **URL Audited** | `[URL]` |
| **Total Issues** | `[TOTAL_ISSUES]` |
| **Critical** | `[CRITICAL_COUNT]` |
| **High** | `[HIGH_COUNT]` |
| **Medium** | `[MEDIUM_COUNT]` |
| **Low** | `[LOW_COUNT]` |
| **Overall Score** | `[SCORE]/100 |

### Quick Assessment

```
[2-3 sentence summary of overall quality and main concerns]
```

---

## 🔴 Critical Issues

> These issues block user flows, violate accessibility, or cause security concerns. Fix immediately.

### Issue CRIT-001: [Issue Title]

| Attribute | Value |
|-----------|-------|
| **Type** | Accessibility / Functional / Security |
| **Location** | `[CSS_SELECTOR]` |
| **WCAG Criterion** | `[CRITERION]` (if applicable) |

**Description:**
```
[Detailed description of the issue]
```

**Current Code:**
```html
[Problematic code snippet]
```

**Recommended Fix:**
```html
[Corrected code snippet]
```

**Assigned To:** `[FRONTEND/BACKEND_TEAM]`
**Estimated Effort:** `[SMALL/MEDIUM/LARGE]`

---

## 🟠 High Issues

> Major UX problems or broken functionality. Fix this sprint.

### Issue HIGH-001: [Issue Title]

| Attribute | Value |
|-----------|-------|
| **Type** | Visual / Interactive / Performance |
| **Location** | `[CSS_SELECTOR]` |

**Description:**
```
[Detailed description]
```

**Evidence:**
- Screenshot: `[PATH_TO_SCREENSHOT]`
- Console Error: `[ERROR_MESSAGE]`

**Recommended Fix:**
```
[Solution description]
```

**Assigned To:** `[FRONTEND/BACKEND_TEAM]`

---

## 🟡 Medium Issues

> Noticeable issues that affect experience. Fix next sprint.

### Issue MED-001: [Issue Title]

| Attribute | Value |
|-----------|-------|
| **Type** | Visual / Content | UX |
| **Location** | `[CSS_SELECTOR]` |

**Description:**
```
[Detailed description]
```

**Recommended Fix:**
```
[Solution description]
```

---

## 🟢 Low Issues / Polish

> Minor improvements for better polish. Nice to have.

### Issue LOW-001: [Issue Title]

**Description:** `[Brief description]`
**Recommendation:** `[Brief recommendation]`

---

## 🤖 AI Content Analysis

### Detected AI Slop Patterns

| Pattern | Count | Location |
|---------|-------|----------|
| Generic phrases | `[COUNT]` | `[LOCATIONS]` |
| Placeholder text | `[COUNT]` | `[LOCATIONS]` |
| Repetitive structure | `[COUNT]` | `[LOCATIONS]` |

### Content Quality Score

| Section | Score | Grade |
|---------|-------|-------|
| Hero section | `[SCORE]` | `[A-F]` |
| About section | `[SCORE]` | `[A-F]` |
| CTA sections | `[SCORE]` | `[A-F]` |

**Recommendations:**
1. [Content recommendation]
2. [Content recommendation]

---

## ♿ Accessibility Summary

### WCAG Compliance

| Level | Pass | Fail | Pass Rate |
|-------|------|------|-----------|
| A (Critical) | `[PASS]` | `[FAIL]` | `[RATE]%` |
| AA (Standard) | `[PASS]` | `[FAIL]` | `[RATE]%` |

### Key Accessibility Issues

1. **[Issue]** - `[Description]`
2. **[Issue]** - `[Description]`
3. **[Issue]** - `[Description]`

---

## 📱 Responsive Testing

### Viewport Results

| Viewport | Status | Notes |
|----------|--------|-------|
| Mobile (375px) | ✅ / ❌ | `[NOTES]` |
| Tablet (768px) | ✅ / ❌ | `[NOTES]` |
| Desktop (1280px) | ✅ / ❌ | `[NOTES]` |
| Large (1920px) | ✅ / ❌ | `[NOTES]` |

### Responsive Issues Found

1. [Issue description at specific viewport]

---

## ⚡ Performance Metrics

| Metric | Value | Status |
|--------|-------|--------|
| Page Load Time | `[TIME]`s | ✅ / ⚠️ / ❌ |
| First Contentful Paint | `[TIME]`s | ✅ / ⚠️ / ❌ |
| Console Errors | `[COUNT]` | ✅ / ⚠️ / ❌ |
| Network Failures | `[COUNT]` | ✅ / ⚠️ / ❌ |

### Console Errors

```
[List of console error messages]
```

### Failed Network Requests

| URL | Status | Type |
|-----|--------|------|
| `[URL]` | `[STATUS]` | `[JS/IMAGE/API]` |

---

## 🎨 Visual Quality

### Layout Issues

| Issue Type | Count | Severity |
|------------|-------|----------|
| Alignment | `[COUNT]` | `[SEVERITY]` |
| Spacing inconsistency | `[COUNT]` | `[SEVERITY]` |
| Overflow | `[COUNT]` | `[SEVERITY]` |
| Z-index conflicts | `[COUNT]` | `[SEVERITY]` |

### Design System Adherence

- [ ] Colors match design system
- [ ] Typography follows guidelines
- [ ] Spacing scale is consistent
- [ ] Components match design specs

---

## 🏷️ Issue Classification Summary

### By Type

| Type | Count |
|------|-------|
| Frontend (CSS/HTML) | `[COUNT]` |
| Frontend (JavaScript) | `[COUNT]` |
| Backend (API) | `[COUNT]` |
| Backend (Server) | `[COUNT]` |
| Content | `[COUNT]` |
| Configuration | `[COUNT]` |

### By Component

| Component | Issues |
|-----------|--------|
| Header/Navigation | `[COUNT]` |
| Hero Section | `[COUNT]` |
| Forms | `[COUNT]` |
| Cards/Lists | `[COUNT]` |
| Footer | `[COUNT]` |
| Modals/Overlays | `[COUNT]` |

---

## 📝 Recommended Actions

### Immediate (This Sprint)

1. [ ] [Critical action item]
2. [ ] [Critical action item]
3. [ ] [High priority action item]

### Short Term (Next Sprint)

1. [ ] [Medium priority action item]
2. [ ] [Medium priority action item]

### Long Term (Backlog)

1. [ ] [Low priority action item]
2. [ ] [Polish/enhancement item]

---

## 🔗 Handoff Information

### For Frontend Team

```
[Specific issues and fixes for frontend developers]
```

**Files Likely Affected:**
- `[FILE_PATH_1]`
- `[FILE_PATH_2]`

### For Backend Team

```
[Specific issues and fixes for backend developers]
```

**APIs Endpoints to Review:**
- `[ENDPOINT_1]`
- `[ENDPOINT_2]`

### For Content Team

```
[Content updates needed]
```

---

## 📎 Attachments

- Full Screenshot: `[PATH]`
- Mobile Screenshot: `[PATH]`
- Accessibility Report: `[PATH]`
- Performance Trace: `[PATH]`

---

## 📚 Related Skills

Pass this report to these skills for fixes:

- **frontend-design** - For visual/layout fixes
- **senior-frontend** - For React/Vue component fixes
- **code-reviewer** - For code quality review
- **laravel-api-expert** - For Laravel backend API issues
- **laravel-backend-expert** - For Laravel server-side issues

---

*Report generated by Frontend UX QA Pro*
*Playwright MCP v[X.X]*
