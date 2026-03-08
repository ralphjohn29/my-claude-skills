# Accessibility Testing Reference

## Table of Contents
- [WCAG 2.1 Quick Reference](#wcag-21-quick-reference)
- [Automated Testing with Playwright](#automated-testing-with-playwright)
- [Color Contrast Testing](#color-contrast-testing)
- [Keyboard Navigation Testing](#keyboard-navigation-testing)
- [Screen Reader Testing](#screen-reader-testing)
- [Form Accessibility](#form-accessibility)
- [Common A11y Issues & Fixes](#common-a11y-issues--fixes)

---

## WCAG 2.1 Quick Reference

### Level A (Minimum)

| Criterion | Description | Test Method |
|-----------|-------------|-------------|
| 1.1.1 Non-text Content | All images have alt text | Check `<img alt="">` |
| 1.3.1 Info and Relationships | Structure via semantics | Check headings, lists |
| 1.3.2 Meaningful Sequence | Logical reading order | Tab through page |
| 1.4.1 Use of Color | Not color-only info | Check grayscale |
| 2.1.1 Keyboard | All functionality via keyboard | Tab navigation |
| 2.1.2 No Keyboard Trap | Can navigate away | Tab through modals |
| 2.4.1 Bypass Blocks | Skip navigation link | Check for skip link |
| 2.4.2 Page Titled | Descriptive page titles | Check `<title>` |
| 2.4.3 Focus Order | Logical focus sequence | Tab order test |
| 2.4.4 Link Purpose | Link text is clear | Read link text |
| 3.1.1 Language of Page | Lang attribute present | Check `<html lang="">` |
| 3.2.1 On Focus | No unexpected context change | Check focus handlers |
| 3.2.2 On Input | No unexpected context change | Check input handlers |
| 3.3.1 Error Identification | Errors are described | Submit invalid form |
| 3.3.2 Labels or Instructions | Form fields labeled | Check all inputs |
| 4.1.1 Parsing | Valid HTML | Validate markup |
| 4.1.2 Name, Role, Value | ARIA is correct | Check custom widgets |

### Level AA (Standard)

| Criterion | Description | Test Method |
|-----------|-------------|-------------|
| 1.4.3 Contrast (Minimum) | 4.5:1 for text | Contrast checker |
| 1.4.4 Resize Text | 200% zoom readable | Zoom and test |
| 1.4.5 Images of Text | Use real text | Check for text in images |
| 2.4.5 Multiple Ways | Multiple navigation options | Check nav methods |
| 2.4.6 Headings and Labels | Descriptive headings | Review headings |
| 2.4.7 Focus Visible | Focus indicator visible | Tab and observe |
| 3.2.3 Consistent Navigation | Same nav across pages | Compare pages |
| 3.2.4 Consistent Identification | Same components same way | Check patterns |
| 3.3.3 Error Suggestion | Suggest fixes for errors | Trigger errors |
| 3.3.4 Error Prevention | Confirm important actions | Check forms |

---

## Automated Testing with Playwright

### Using axe-core with Playwright

```javascript
// Install: npm install @axe-core/playwright

import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

test('Accessibility audit', async ({ page }) => {
  await page.goto('https://example.com');

  const accessibilityScanResults = await new AxeBuilder({ page })
    .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
    .analyze();

  expect(accessibilityScanResults.violations).toEqual([]);
});
```

### Testing Specific Components

```javascript
test('Modal accessibility', async ({ page }) => {
  await page.goto('https://example.com');
  await page.click('button[aria-label="Open modal"]');

  // Wait for modal to appear
  await page.waitForSelector('[role="dialog"]');

  const results = await new AxeBuilder({ page })
    .include('[role="dialog"]')
    .analyze();

  expect(results.violations).toEqual([]);
});
```

### Excluding Known Issues

```javascript
const results = await new AxeBuilder({ page })
  .exclude('#legacy-component') // Known issues, scheduled for refactor
  .disableRules(['color-contrast']) // If testing in progress
  .analyze();
```

---

## Color Contrast Testing

### Contrast Requirements

| Text Type | Minimum Ratio | Best Practice |
|-----------|---------------|---------------|
| Normal text (<18px) | 4.5:1 | 7:1 (AAA) |
| Large text (≥18px bold or ≥24px) | 3:1 | 4.5:1 (AAA) |
| UI components | 3:1 | 4.5:1 |
| Graphical objects | 3:1 | 4.5:1 |

### Playwright Contrast Check

```javascript
test('Color contrast check', async ({ page }) => {
  await page.goto('https://example.com');

  const contrastIssues = await page.evaluate(() => {
    const issues = [];
    const elements = document.querySelectorAll('p, h1, h2, h3, h4, h5, h6, a, button, label, span');

    elements.forEach(el => {
      const style = getComputedStyle(el);
      const color = style.color;
      const bgColor = style.backgroundColor;

      // Simple check - for accurate results use axe-core
      // This is a basic heuristic
      if (color === bgColor) {
        issues.push({
          element: el.tagName,
          text: el.textContent?.substring(0, 50),
          issue: 'Text color matches background'
        });
      }
    });

    return issues;
  });

  expect(contrastIssues).toHaveLength(0);
});
```

### Common Contrast Failures

```css
/* FAIL: Light gray on white */
.button { color: #ccc; background: #fff; } /* Ratio: 1.6:1 */

/* PASS: Proper contrast */
.button { color: #595959; background: #fff; } /* Ratio: 4.5:1 */

/* FAIL: Placeholder text */
input::placeholder { color: #aaa; } /* Often fails */

/* PASS: Darker placeholder */
input::placeholder { color: #767676; } /* 4.5:1 minimum */
```

---

## Keyboard Navigation Testing

### Tab Order Test

```javascript
test('Tab order is logical', async ({ page }) => {
  await page.goto('https://example.com');

  // Get all focusable elements
  const focusableSelectors = [
    'a[href]',
    'button:not([disabled])',
    'input:not([disabled])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])'
  ].join(', ');

  const elements = await page.$$(focusableSelectors);
  const tabOrder = [];

  for (let i = 0; i < Math.min(10, elements.length); i++) {
    await page.keyboard.press('Tab');
    const focused = await page.evaluateHandle(() => document.activeElement);
    const tagName = await focused.evaluate(el => el.tagName);
    const text = await focused.evaluate(el => el.textContent?.substring(0, 30));
    tabOrder.push({ tagName, text });
  }

  console.log('Tab order:', tabOrder);

  // Verify logical order (e.g., header -> nav -> main -> footer)
  expect(tabOrder[0].tagName).toBe('A'); // Skip link or first nav item
});
```

### Keyboard Trap Test

```javascript
test('No keyboard trap in modal', async ({ page }) => {
  await page.goto('https://example.com');

  // Open modal
  await page.click('[data-testid="open-modal"]');
  await page.waitForSelector('[role="dialog"]');

  // Tab through modal
  for (let i = 0; i < 20; i++) {
    await page.keyboard.press('Tab');
  }

  // Close modal with Escape
  await page.keyboard.press('Escape');

  // Verify modal is closed
  await expect(page.locator('[role="dialog"]')).not.toBeVisible();
});
```

### Focus Visibility Test

```javascript
test('Focus indicators are visible', async ({ page }) => {
  await page.goto('https://example.com');

  const focusableElements = await page.$$('a, button, input');

  for (const element of focusableElements.slice(0, 5)) {
    await element.focus();

    // Check for focus styles
    const outline = await element.evaluate(el => {
      const style = getComputedStyle(el);
      return {
        outline: style.outline,
        outlineWidth: style.outlineWidth,
        boxShadow: style.boxShadow
      };
    });

    // Should have either outline or box-shadow for focus
    const hasFocusIndicator =
      outline.outline !== 'none' ||
      outline.outlineWidth !== '0px' ||
      outline.boxShadow !== 'none';

    expect(hasFocusIndicator).toBeTruthy();
  }
});
```

---

## Screen Reader Testing

### Landmark Regions

```html
<!-- Required landmarks for screen readers -->
<header role="banner">
  <nav role="navigation" aria-label="Main navigation">
    <!-- Navigation items -->
  </nav>
</header>

<main role="main">
  <article>
    <h1>Page Title</h1>
    <!-- Content -->
  </article>
</main>

<aside role="complementary">
  <!-- Sidebar content -->
</aside>

<footer role="contentinfo">
  <!-- Footer content -->
</footer>
```

### Heading Hierarchy Test

```javascript
test('Heading hierarchy is correct', async ({ page }) => {
  await page.goto('https://example.com');

  const headings = await page.evaluate(() => {
    const h = Array.from(document.querySelectorAll('h1, h2, h3, h4, h5, h6'));
    return h.map(el => ({
      tag: el.tagName,
      text: el.textContent?.substring(0, 50)
    }));
  });

  // Should have exactly one h1
  const h1Count = headings.filter(h => h.tag === 'H1').length;
  expect(h1Count).toBe(1);

  // Should not skip levels (e.g., h1 -> h3)
  const levels = headings.map(h => parseInt(h.tag.charAt(1)));
  for (let i = 1; i < levels.length; i++) {
    const skip = levels[i] - levels[i - 1];
    expect(skip).toBeLessThanOrEqual(1);
  }
});
```

### Alt Text Check

```javascript
test('All images have alt text', async ({ page }) => {
  await page.goto('https://example.com');

  const imagesWithoutAlt = await page.evaluate(() => {
    const images = Array.from(document.querySelectorAll('img'));
    return images
      .filter(img => !img.hasAttribute('alt'))
      .map(img => ({
        src: img.src,
        width: img.width,
        height: img.height
      }));
  });

  expect(imagesWithoutAlt).toHaveLength(0);
});

test('Decorative images have empty alt', async ({ page }) => {
  await page.goto('https://example.com');

  const decorativeImages = await page.evaluate(() => {
    // Images that are likely decorative
    const images = Array.from(document.querySelectorAll('img'));
    return images
      .filter(img => {
        const alt = img.getAttribute('alt');
        const role = img.getAttribute('role');
        return role === 'presentation' || role === 'none';
      })
      .map(img => img.src);
  });

  // Decorative images should have empty alt
  for (const src of decorativeImages) {
    const img = await page.$(`img[src="${src}"]`);
    const alt = await img?.getAttribute('alt');
    expect(alt).toBe('');
  }
});
```

---

## Form Accessibility

### Label Association Test

```javascript
test('All form inputs have labels', async ({ page }) => {
  await page.goto('https://example.com/form');

  const inputIssues = await page.evaluate(() => {
    const inputs = Array.from(document.querySelectorAll('input, select, textarea'));
    const issues = [];

    inputs.forEach(input => {
      const hasLabel = input.hasAttribute('id') &&
        document.querySelector(`label[for="${input.id}"]`);

      const hasAriaLabel = input.hasAttribute('aria-label') ||
        input.hasAttribute('aria-labelledby');

      const isHidden = input.type === 'hidden';

      if (!hasLabel && !hasAriaLabel && !isHidden) {
        issues.push({
          type: input.type || input.tagName,
          id: input.id,
          name: input.name
        });
      }
    });

    return issues;
  });

  expect(inputIssues).toHaveLength(0);
});
```

### Required Field Indicators

```javascript
test('Required fields are properly marked', async ({ page }) => {
  await page.goto('https://example.com/form');

  const requiredFields = await page.$$('input[required], select[required], textarea[required]');

  for (const field of requiredFields) {
    // Check for aria-required or required attribute
    const hasRequired = await field.evaluate(el => {
      return el.hasAttribute('required') || el.getAttribute('aria-required') === 'true';
    });

    expect(hasRequired).toBeTruthy();

    // Check that label indicates required (visual indicator)
    const id = await field.getAttribute('id');
    const label = await page.$(`label[for="${id}"]`);
    if (label) {
      const labelText = await label.textContent();
      // Should have * or (required) or similar
      const hasRequiredIndicator = labelText?.includes('*') ||
        labelText?.toLowerCase().includes('required');
      // This is a best practice check, not a hard fail
    }
  }
});
```

### Error Messages Test

```javascript
test('Form errors are accessible', async ({ page }) => {
  await page.goto('https://example.com/form');

  // Submit empty form to trigger errors
  await page.click('button[type="submit"]');

  // Check for error messages
  const errors = await page.evaluate(() => {
    const errorElements = Array.from(document.querySelectorAll(
      '[role="alert"], .error, .error-message, [aria-invalid="true"]'
    ));

    return errorElements.map(el => ({
      text: el.textContent,
      role: el.getAttribute('role'),
      ariaInvalid: el.getAttribute('aria-invalid')
    }));
  });

  // Errors should be announced
  expect(errors.length).toBeGreaterThan(0);

  // Check that inputs are marked invalid
  const invalidInputs = await page.$$('input[aria-invalid="true"]');
  expect(invalidInputs.length).toBeGreaterThan(0);
});
```

---

## Common A11y Issues & Fixes

### Issue: Missing Skip Link

```html
<!-- BAD: No skip link -->
<body>
  <header>...</header>
  <main>...</main>
</body>

<!-- GOOD: Skip link provided -->
<body>
  <a href="#main-content" class="skip-link">Skip to main content</a>
  <header>...</header>
  <main id="main-content">...</main>
</body>

<style>
.skip-link {
  position: absolute;
  top: -40px;
  left: 0;
  background: #000;
  color: #fff;
  padding: 8px;
  z-index: 100;
}
.skip-link:focus {
  top: 0;
}
</style>
```

### Issue: Empty Links/Buttons

```html
<!-- BAD: Empty link -->
<a href="/home"><i class="icon-home"></i></a>

<!-- GOOD: Descriptive link -->
<a href="/home">
  <i class="icon-home" aria-hidden="true"></i>
  <span class="sr-only">Home</span>
</a>

<!-- BAD: Empty button -->
<button><i class="icon-close"></i></button>

<!-- GOOD: Labeled button -->
<button aria-label="Close dialog">
  <i class="icon-close" aria-hidden="true"></i>
</button>
```

### Issue: Incorrect ARIA

```html
<!-- BAD: Redundant ARIA -->
<button role="button">Click me</button>
<nav role="navigation">...</nav>

<!-- GOOD: Use semantic HTML -->
<button>Click me</button>
<nav>...</nav>

<!-- BAD: aria-hidden on focusable -->
<button aria-hidden="true">Can still be focused!</button>

<!-- GOOD: Hide properly -->
<button aria-hidden="true" tabindex="-1">Now truly hidden</button>
```

### Issue: Autoplay Media

```html
<!-- BAD: Autoplay with sound -->
<video autoplay src="video.mp4"></video>

<!-- GOOD: No autoplay or muted -->
<video autoplay muted src="video.mp4"></video>

<!-- Or provide controls -->
<video controls src="video.mp4"></video>
```

---

## Quick Accessibility Checklist

```
[ ] Page has language attribute (<html lang="en">)
[ ] Page has unique, descriptive title
[ ] Exactly one H1 per page
[ ] Heading hierarchy is logical (no skipping)
[ ] All images have appropriate alt text
[ ] All form inputs have associated labels
[ ] Color contrast meets 4.5:1 minimum
[ ] Focus indicators are visible
[ ] All functionality works with keyboard
[ ] No keyboard traps
[ ] Skip navigation link provided
[ ] ARIA is used correctly (not redundantly)
[ ] Error messages are clear and associated with inputs
[ ] Touch targets are at least 44x44px
[ ] Content is readable at 200% zoom
```
