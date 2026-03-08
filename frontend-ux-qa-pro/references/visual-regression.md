# Visual Regression Testing Reference

## Table of Contents
- [Overview](#overview)
- [Playwright Screenshot Testing](#playwright-screenshot-testing)
- [Layout Analysis Scripts](#layout-analysis-scripts)
- [Spacing & Alignment Checks](#spacing--alignment-checks)
- [Responsive Testing](#responsive-testing)
- [Cross-Browser Visual Testing](#cross-browser-visual-testing)

---

## Overview

Visual regression testing ensures the UI renders correctly across:
- Different screen sizes
- Different browsers
- Different states (hover, focus, active)
- Theme variations (light/dark mode)

### Types of Visual Tests

| Type | Purpose | Tools |
|------|---------|-------|
| Pixel-perfect | Exact match comparison | `toHaveScreenshot()` |
| Layout | Structure and positioning | Custom scripts |
| Component | Isolated component rendering | Storybook + Playwright |
| Full-page | Complete page renders | Full page screenshots |

---

## Playwright Screenshot Testing

### Basic Screenshot Test

```javascript
import { test, expect } from '@playwright/test';

test('Homepage visual regression', async ({ page }) => {
  await page.goto('https://example.com');

  // Wait for fonts and images to load
  await page.waitForLoadState('networkidle');

  // Take and compare screenshot
  await expect(page).toHaveScreenshot('homepage.png', {
    maxDiffPixels: 100,
    animations: 'disabled',
  });
});
```

### Element-Level Screenshots

```javascript
test('Button states', async ({ page }) => {
  await page.goto('https://example.com/components');

  const button = page.locator('.primary-button');

  // Default state
  await expect(button).toHaveScreenshot('button-default.png');

  // Hover state
  await button.hover();
  await expect(button).toHaveScreenshot('button-hover.png');

  // Focus state
  await button.focus();
  await expect(button).toHaveScreenshot('button-focus.png');

  // Active/pressed state
  await button.click({ delay: 100 });
  await expect(button).toHaveScreenshot('button-active.png');
});
```

### Full Page Screenshots

```javascript
test('Full page screenshot', async ({ page }) => {
  await page.goto('https://example.com/long-page');

  // Full page including scrollable content
  await expect(page).toHaveScreenshot('full-page.png', {
    fullPage: true,
    maxDiffPixelRatio: 0.02, // Allow 2% difference
  });
});
```

### Handling Dynamic Content

```javascript
test('Hide dynamic content', async ({ page }) => {
  await page.goto('https://example.com');

  // Hide elements that change (dates, ads, etc.)
  await page.addStyleTag({
    content: `
      .timestamp, .ad-banner, .live-feed {
        visibility: hidden !important;
      }
    `
  });

  await expect(page).toHaveScreenshot('homepage-stable.png');
});
```

### Masking Sensitive Areas

```javascript
test('Mask sensitive content', async ({ page }) => {
  await page.goto('https://example.com/profile');

  await expect(page).toHaveScreenshot('profile-page.png', {
    mask: [
      page.locator('.user-avatar'),
      page.locator('.email-address'),
    ],
  });
});
```

---

## Layout Analysis Scripts

### Grid/Flex Layout Integrity

```javascript
test('Grid layout is intact', async ({ page }) => {
  await page.goto('https://example.com');

  const layoutIssues = await page.evaluate(() => {
    const issues = [];

    // Check for broken grids
    document.querySelectorAll('[class*="grid"]').forEach(grid => {
      const children = grid.children;
      const gridStyle = getComputedStyle(grid);
      const columns = gridStyle.gridTemplateColumns.split(' ').length;

      // Check if children overflow grid
      for (const child of children) {
        const rect = child.getBoundingClientRect();
        const gridRect = grid.getBoundingClientRect();

        if (rect.right > gridRect.right + 1) {
          issues.push({
            type: 'grid-overflow',
            element: child.className,
            message: 'Grid child overflows container'
          });
        }
      }
    });

    return issues;
  });

  expect(layoutIssues).toHaveLength(0);
});
```

### Z-Index Conflict Detection

```javascript
test('No z-index conflicts', async ({ page }) => {
  await page.goto('https://example.com');

  const zIssues = await page.evaluate(() => {
    const issues = [];
    const stackedElements = [];

    // Get all elements with z-index
    document.querySelectorAll('*').forEach(el => {
      const style = getComputedStyle(el);
      const zIndex = parseInt(style.zIndex);

      if (!isNaN(zIndex) && zIndex > 0) {
        stackedElements.push({
          element: el.tagName + (el.className ? '.' + el.className.split(' ')[0] : ''),
          zIndex,
          position: style.position
        });
      }
    });

    // Check for overlapping z-index values
    const zIndexGroups = {};
    stackedElements.forEach(el => {
      if (!zIndexGroups[el.zIndex]) zIndexGroups[el.zIndex] = [];
      zIndexGroups[el.zIndex].push(el);
    });

    Object.entries(zIndexGroups).forEach(([z, elements]) => {
      if (elements.length > 1) {
        issues.push({
          type: 'z-index-conflict',
          zIndex: z,
          elements: elements.map(e => e.element),
          message: 'Multiple elements share the same z-index'
        });
      }
    });

    return issues;
  });

  // Log for review, but don't fail
  if (zIssues.length > 0) {
    console.log('Z-index conflicts found:', zIssues);
  }
});
```

### Overflow Detection

```javascript
test('No horizontal overflow', async ({ page }) => {
  await page.setViewportSize({ width: 1920, height: 1080 });
  await page.goto('https://example.com');

  const overflowElements = await page.evaluate(() => {
    const issues = [];
    const bodyWidth = document.body.scrollWidth;
    const viewportWidth = window.innerWidth;

    if (bodyWidth > viewportWidth) {
      // Find elements causing overflow
      document.querySelectorAll('*').forEach(el => {
        const rect = el.getBoundingClientRect();
        if (rect.right > viewportWidth + 5) {
          issues.push({
            tag: el.tagName,
            class: el.className?.toString().split(' ')[0],
            right: Math.round(rect.right),
            viewportWidth
          });
        }
      });
    }

    return {
      hasOverflow: bodyWidth > viewportWidth,
      bodyWidth,
      viewportWidth,
      culprits: issues.slice(0, 10) // Top 10
    };
  });

  expect(overflowElements.hasOverflow).toBeFalsy();
});
```

---

## Spacing & Alignment Checks

### Consistent Spacing Test

```javascript
test('Spacing is consistent', async ({ page }) => {
  await page.goto('https://example.com');

  const spacingAnalysis = await page.evaluate(() => {
    const results = {
      sections: [],
      inconsistencies: []
    };

    // Check section spacing
    const sections = document.querySelectorAll('section, .section');
    const gaps = [];

    sections.forEach((section, i) => {
      if (i > 0) {
        const prevRect = sections[i - 1].getBoundingClientRect();
        const currRect = section.getBoundingClientRect();
        gaps.push(Math.round(currRect.top - prevRect.bottom));
      }
    });

    // Check if gaps are consistent
    const uniqueGaps = [...new Set(gaps)];
    if (uniqueGaps.length > 2) {
      results.inconsistencies.push({
        type: 'section-gaps',
        gaps: uniqueGaps,
        message: 'Section gaps are inconsistent'
      });
    }

    results.sections = gaps;
    return results;
  });

  // Log for review
  console.log('Spacing analysis:', spacingAnalysis);
});
```

### Text Alignment Check

```javascript
test('Text alignment is correct', async ({ page }) => {
  await page.goto('https://example.com');

  const alignmentIssues = await page.evaluate(() => {
    const issues = [];

    // Check heading alignments
    const headings = document.querySelectorAll('h1, h2, h3');
    let prevLeft = null;
    let prevType = null;

    headings.forEach(h => {
      const rect = h.getBoundingClientRect();
      const style = getComputedStyle(h);
      const textAlign = style.textAlign;

      // Check if headings in same container have same alignment
      if (prevLeft !== null && Math.abs(rect.left - prevLeft) > 5) {
        issues.push({
          type: 'heading-misalignment',
          current: h.tagName,
          previous: prevType,
          currentLeft: Math.round(rect.left),
          previousLeft: prevLeft
        });
      }

      prevLeft = Math.round(rect.left);
      prevType = h.tagName;
    });

    return issues;
  });

  // Review and report
  if (alignmentIssues.length > 0) {
    console.log('Alignment issues:', alignmentIssues);
  }
});
```

### Button Alignment Check

```javascript
test('Buttons are aligned', async ({ page }) => {
  await page.goto('https://example.com');

  const buttonGroups = await page.evaluate(() => {
    const results = [];

    // Find button groups (buttons in same container)
    document.querySelectorAll('section, form, .button-group').forEach(container => {
      const buttons = container.querySelectorAll('button, .btn, a[role="button"]');
      if (buttons.length > 1) {
        const positions = Array.from(buttons).map(btn => ({
          text: btn.textContent?.trim().substring(0, 20),
          top: btn.getBoundingClientRect().top,
          bottom: btn.getBoundingClientRect().bottom,
          height: btn.getBoundingClientRect().height
        }));

        // Check vertical alignment
        const tops = positions.map(p => Math.round(p.top));
        const bottoms = positions.map(p => Math.round(p.bottom));
        const heights = positions.map(p => Math.round(p.height));

        const alignedTop = tops.every(t => Math.abs(t - tops[0]) < 3);
        const alignedBottom = bottoms.every(b => Math.abs(b - bottoms[0]) < 3);
        const sameHeight = heights.every(h => Math.abs(h - heights[0]) < 3);

        if (!alignedTop || !alignedBottom || !sameHeight) {
          results.push({
            container: container.className || container.tagName,
            buttons: positions,
            issues: {
              alignedTop,
              alignedBottom,
              sameHeight
            }
          });
        }
      }
    });

    return results;
  });

  expect(buttonGroups).toHaveLength(0);
});
```

### Icon Alignment Check

```javascript
test('Icons are aligned with text', async ({ page }) => {
  await page.goto('https://example.com');

  const iconIssues = await page.evaluate(() => {
    const issues = [];

    // Find icon + text combinations
    document.querySelectorAll('button, a, .icon-text').forEach(container => {
      const icons = container.querySelectorAll('svg, i, .icon, [class*="icon"]');
      const text = container.textContent?.trim();

      if (icons.length > 0 && text) {
        const containerRect = container.getBoundingClientRect();
        const containerStyle = getComputedStyle(container);

        icons.forEach(icon => {
          const iconRect = icon.getBoundingClientRect();
          const containerMiddle = containerRect.top + containerRect.height / 2;
          const iconMiddle = iconRect.top + iconRect.height / 2;

          // Check if icon is vertically centered
          if (Math.abs(containerMiddle - iconMiddle) > 3) {
            issues.push({
              container: container.tagName + (container.className ? '.' + container.className.split(' ')[0] : ''),
              iconOffset: Math.round(containerMiddle - iconMiddle),
              message: 'Icon is not vertically centered'
            });
          }
        });
      }
    });

    return issues;
  });

  // Log for review
  console.log('Icon alignment issues:', iconIssues);
});
```

---

## Responsive Testing

### Viewport Testing

```javascript
import { test, expect } from '@playwright/test';

const viewports = [
  { name: 'mobile', width: 375, height: 667 },
  { name: 'mobile-landscape', width: 667, height: 375 },
  { name: 'tablet', width: 768, height: 1024 },
  { name: 'tablet-landscape', width: 1024, height: 768 },
  { name: 'desktop', width: 1280, height: 720 },
  { name: 'desktop-large', width: 1920, height: 1080 },
];

for (const viewport of viewports) {
  test(`Layout at ${viewport.name}`, async ({ page }) => {
    await page.setViewportSize(viewport);
    await page.goto('https://example.com');
    await page.waitForLoadState('networkidle');

    await expect(page).toHaveScreenshot(`layout-${viewport.name}.png`, {
      maxDiffPixels: 100,
    });
  });
}
```

### Mobile-Specific Checks

```javascript
test('Mobile layout integrity', async ({ page }) => {
  await page.setViewportSize({ width: 375, height: 667 });
  await page.goto('https://example.com');

  const mobileIssues = await page.evaluate(() => {
    const issues = [];

    // Check font sizes (minimum 16px for inputs to prevent zoom)
    document.querySelectorAll('input, select, textarea').forEach(input => {
      const fontSize = parseInt(getComputedStyle(input).fontSize);
      if (fontSize < 16) {
        issues.push({
          type: 'small-input-font',
          element: input.name || input.id || input.type,
          fontSize,
          message: 'Input font size < 16px causes zoom on iOS'
        });
      }
    });

    // Check touch targets (minimum 44x44)
    document.querySelectorAll('button, a, input, select').forEach(el => {
      const rect = el.getBoundingClientRect();
      if (rect.width < 44 || rect.height < 44) {
        issues.push({
          type: 'small-touch-target',
          element: el.tagName,
          width: Math.round(rect.width),
          height: Math.round(rect.height),
          message: 'Touch target smaller than 44x44'
        });
      }
    });

    // Check for horizontal scroll
    const bodyWidth = document.body.scrollWidth;
    const viewportWidth = window.innerWidth;
    if (bodyWidth > viewportWidth) {
      issues.push({
        type: 'horizontal-scroll',
        bodyWidth,
        viewportWidth,
        message: 'Page has horizontal scroll on mobile'
      });
    }

    return issues;
  });

  expect(mobileIssues).toHaveLength(0);
});
```

---

## Cross-Browser Visual Testing

### Browser-Specific Screenshots

```javascript
// playwright.config.ts
export default {
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] },
    },
    {
      name: 'webkit',
      use: { ...devices['Desktop Safari'] },
    },
  ],
};

// test file
test('Cross-browser visual test', async ({ page, browserName }) => {
  await page.goto('https://example.com');

  await expect(page).toHaveScreenshot(`homepage-${browserName}.png`, {
    maxDiffPixels: browserName === 'webkit' ? 200 : 100, // Safari may render slightly differently
  });
});
```

### Font Rendering Differences

```javascript
test('Handle font differences', async ({ page, browserName }) => {
  await page.goto('https://example.com');

  // Use threshold that accounts for font rendering differences
  await expect(page).toHaveScreenshot(`page-${browserName}.png`, {
    maxDiffPixelRatio: 0.01, // 1% difference allowed
    threshold: 0.3, // Per-pixel threshold
    animations: 'disabled',
  });
});
```

---

## Screenshot Organization

```
tests/
├── visual/
│   ├── homepage.spec.ts
│   ├── components/
│   │   ├── buttons.spec.ts
│   │   ├── forms.spec.ts
│   │   └── navigation.spec.ts
│   └── pages/
│       ├── landing.spec.ts
│       └── dashboard.spec.ts
├── snapshots/           # Baseline images
│   ├── homepage.spec.ts/
│   │   ├── homepage-chrome.png
│   │   ├── homepage-firefox.png
│   │   └── homepage-webkit.png
│   └── components/
└── snapshot-diff/       # Diff images on failure
```

---

## Best Practices

### Do's

1. **Wait for stability** - Use `waitForLoadState('networkidle')`
2. **Disable animations** - `animations: 'disabled'`
3. **Use consistent viewports** - Same size for baselines
4. **Mask dynamic content** - Dates, timestamps, ads
5. **Set reasonable thresholds** - Balance strictness vs flakiness

### Don'ts

1. **Don't test every page** - Focus on critical paths
2. **Don't use 0 tolerance** - Will be flaky
3. **Don't ignore failures** - Investigate diffs
4. **Don't skip CI** - Run on every PR
5. **Don't forget updates** - Update baselines on intentional changes
