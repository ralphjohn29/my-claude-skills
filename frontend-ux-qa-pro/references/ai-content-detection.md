# AI Content Detection & Quality Analysis

## Table of Contents
- [What is "AI Slop"](#what-is-ai-slop)
- [Detection Patterns](#detection-patterns)
- [Automated Detection Scripts](#automated-detection-scripts)
- [Content Quality Metrics](#content-quality-metrics)
- [Remediation Guide](#remediation-guide)

---

## What is "AI Slop"

AI Slop refers to low-quality, generic content that indicates rushed AI-generated text without human review.

### Common Indicators

| Pattern | Example | Issue |
|---------|---------|-------|
| Generic openers | "In today's digital age..." | Overused AI phrase |
| Empty superlatives | "cutting-edge", "innovative", "revolutionary" | Lacks specificity |
| Repetitive structure | Same sentence structure throughout | No variety |
| Vague statements | "We provide solutions" | No concrete value |
| Unfinished placeholders | "[Your Name]", "Lorem ipsum" | Not reviewed |
| Inconsistent tone | Formal then casual | No editorial pass |
| Generic CTAs | "Learn more" everywhere | No context |

---

## Detection Patterns

### Text Patterns to Flag

```javascript
const AI_SLOP_PATTERNS = {
  // Generic AI phrases
  genericPhrases: [
    /in today'?s (digital|modern|fast-paced) (world|age|landscape)/i,
    /(unlock|unleash|tap into|leverage) (the power|potential) of/i,
    /(revolutionary|game-changing|cutting-edge|state-of-the-art)/i,
    /(comprehensive|holistic|seamless) (solution|approach|experience)/i,
    /(elevate|transform|revolutionize) (your|the) (business|workflow|experience)/i,
    /(empower|enable) (you|users|teams) to/i,
    /at (the )?forefront of innovation/i,
    /(bridge|close) the gap between/i,
    /(paradigm|landscape|ecosystem) (shift|change)/i,
    /seamlessly (integrate|connect|blend)/i,
  ],

  // Placeholder patterns
  placeholders: [
    /\[(your|company|product|name|email)\]/i,
    /lorem ipsum/i,
    /dolor sit amet/i,
    /\[insert .* here\]/i,
    /todo:/i,
    /tbd/i,
    /coming soon/i,
    /\.\.\./,  // Ellipsis as placeholder
  ],

  // Repetition patterns
  repetition: [
    /(.)\1{4,}/,  // Character repetition
    /(\b\w+\b)(\s+\1){2,}/i,  // Word repetition
  ],

  // Generic CTAs
  genericCTAs: [
    /^click here$/i,
    /^learn more$/i,
    /^read more$/i,
    /^submit$/i,
    /^get started$/i,
  ],
};
```

### Content Structure Analysis

```javascript
const CONTENT_ISSUES = {
  // Structural problems
  structure: {
    tooShort: 50,          // Minimum character count for paragraphs
    tooLong: 500,          // Maximum for readability
    sentenceLength: 30,    // Average sentence length
    paragraphCount: { min: 1, max: 10 }, // Per section
  },

  // Readability issues
  readability: {
    passiveVoice: /was|were|been|being|is|are|was|were/gi,
    jargonOveruse: /(synergy|leverage|ecosystem|paradigm)/gi,
    buzzwords: /(disruptive|innovative|scalable|robust)/gi,
  },

  // Consistency issues
  consistency: {
    mixedCase: /\b([A-Z][a-z]+[A-Z][a-z]+)\b/, // camelCase in text
    inconsistentPunctuation: /[.!?]{2,}/,
    mixedTone: /(?=.*\b(formal|professional)\b)(?=.*\b(awesome|cool|super)\b)/i,
  },
};
```

---

## Automated Detection Scripts

### Playwright Content Audit

```javascript
const { test } = require('@playwright/test');

test('AI Slop Detection Audit', async ({ page }) => {
  await page.goto('https://example.com');

  const contentAudit = await page.evaluate(() => {
    const results = {
      genericPhrases: [],
      placeholders: [],
      repetition: [],
      emptyContent: [],
      shortContent: [],
      recommendations: []
    };

    // Get all text content
    const textElements = document.querySelectorAll('p, h1, h2, h3, h4, h5, h6, span, a, button, label');

    // Generic AI phrases detector
    const genericPhrases = [
      /in today'?s (digital|modern|fast-paced) (world|age)/i,
      /(unlock|unleash|tap into) (the power|potential) of/i,
      /(revolutionary|game-changing|cutting-edge)/i,
      /(comprehensive|holistic|seamless) solution/i,
      /(elevate|transform|revolutionize) your/i,
      /(empower|enable) (you|users) to/i,
      /at the forefront of innovation/i,
      /bridge the gap between/i,
    ];

    // Placeholder detector
    const placeholders = [
      /\[(your|company|product|name)\]/i,
      /lorem ipsum/i,
      /dolor sit amet/i,
      /\[insert .* here\]/i,
      /todo:/i,
      /tbd/i,
    ];

    textElements.forEach(el => {
      const text = el.textContent?.trim();
      if (!text || text.length < 5) return;

      // Check for generic phrases
      genericPhrases.forEach(pattern => {
        if (pattern.test(text)) {
          results.genericPhrases.push({
            element: el.tagName,
            text: text.substring(0, 100),
            pattern: pattern.source
          });
        }
      });

      // Check for placeholders
      placeholders.forEach(pattern => {
        if (pattern.test(text)) {
          results.placeholders.push({
            element: el.tagName,
            text: text.substring(0, 100),
            pattern: pattern.source
          });
        }
      });

      // Check for very short content (likely incomplete)
      if (el.tagName === 'P' && text.length < 20) {
        results.shortContent.push({
          element: el.tagName,
          text: text,
          length: text.length
        });
      }
    });

    // Check for empty links/buttons
    document.querySelectorAll('a, button').forEach(el => {
      const text = el.textContent?.trim();
      const ariaLabel = el.getAttribute('aria-label');
      const title = el.getAttribute('title');

      if (!text && !ariaLabel && !title) {
        results.emptyContent.push({
          element: el.tagName,
          type: el.tagName === 'A' ? 'link' : 'button',
          href: el.href || el.type
        });
      }
    });

    // Generate recommendations
    if (results.genericPhrases.length > 3) {
      results.recommendations.push('Consider rewriting content - multiple generic AI phrases detected');
    }
    if (results.placeholders.length > 0) {
      results.recommendations.push('Remove placeholder content before launch');
    }
    if (results.emptyContent.length > 0) {
      results.recommendations.push('Add text or aria-labels to empty interactive elements');
    }

    return results;
  });

  console.log('Content Audit Results:', JSON.stringify(contentAudit, null, 2));

  // Assertions
  expect(contentAudit.placeholders).toHaveLength(0);
  expect(contentAudit.emptyContent.length).toBeLessThan(3);
});
```

### Content Quality Score Calculator

```javascript
function calculateContentQualityScore(content) {
  let score = 100;
  const issues = [];

  // Deduct for generic phrases (-5 each)
  const genericCount = countGenericPhrases(content);
  score -= genericCount * 5;
  if (genericCount > 0) issues.push(`${genericCount} generic phrase(s) found`);

  // Deduct for placeholders (-20 each, critical)
  const placeholderCount = countPlaceholders(content);
  score -= placeholderCount * 20;
  if (placeholderCount > 0) issues.push(`${placeholderCount} placeholder(s) found`);

  // Deduct for short content (-2 each)
  const shortParagraphs = countShortParagraphs(content);
  score -= shortParagraphs * 2;
  if (shortParagraphs > 0) issues.push(`${shortParagraphs} short paragraph(s)`);

  // Deduct for repetition (-10)
  if (hasRepetition(content)) {
    score -= 10;
    issues.push('Repetitive content detected');
  }

  // Deduct for readability issues (-5 each)
  const readabilityIssues = checkReadability(content);
  score -= readabilityIssues.length * 5;
  issues.push(...readabilityIssues);

  return {
    score: Math.max(0, score),
    grade: getGrade(score),
    issues,
    recommendation: getRecommendation(score)
  };
}

function getGrade(score) {
  if (score >= 90) return 'A';
  if (score >= 80) return 'B';
  if (score >= 70) return 'C';
  if (score >= 60) return 'D';
  return 'F';
}

function getRecommendation(score) {
  if (score >= 90) return 'Content is high quality';
  if (score >= 70) return 'Minor improvements recommended';
  if (score >= 50) return 'Significant editing needed';
  return 'Content requires major revision';
}
```

### Page-Level Content Audit

```javascript
test('Full page content quality audit', async ({ page }) => {
  await page.goto('https://example.com');

  const audit = await page.evaluate(() => {
    // Extract all visible text
    const getVisibleText = () => {
      const walker = document.createTreeWalker(
        document.body,
        NodeFilter.SHOW_TEXT,
        {
          acceptNode: (node) => {
            // Skip script, style, and hidden elements
            const parent = node.parentElement;
            if (!parent) return NodeFilter.FILTER_REJECT;
            const style = getComputedStyle(parent);
            if (style.display === 'none' || style.visibility === 'hidden') {
              return NodeFilter.FILTER_REJECT;
            }
            if (['SCRIPT', 'STYLE', 'NOSCRIPT'].includes(parent.tagName)) {
              return NodeFilter.FILTER_REJECT;
            }
            return NodeFilter.FILTER_ACCEPT;
          }
        }
      );

      const texts = [];
      while (walker.nextNode()) {
        const text = walker.currentNode.textContent?.trim();
        if (text && text.length > 2) {
          texts.push(text);
        }
      }
      return texts.join(' ');
    };

    const fullText = getVisibleText();
    const wordCount = fullText.split(/\s+/).length;

    // Calculate metrics
    const metrics = {
      wordCount,
      characterCount: fullText.length,
      averageWordLength: fullText.replace(/\s/g, '').length / wordCount,
      sentenceCount: (fullText.match(/[.!?]+/g) || []).length,
    };

    // Detect issues
    const issues = [];

    // Check for AI slop phrases
    const slopPhrases = [
      'in today\'s digital age',
      'at the forefront',
      'cutting-edge solution',
      'comprehensive approach',
      'seamless experience',
    ];

    slopPhrases.forEach(phrase => {
      if (fullText.toLowerCase().includes(phrase)) {
        issues.push({ type: 'ai-phrase', phrase });
      }
    });

    // Check for placeholders
    if (/lorem ipsum|\[your|\[company|todo:|tbd/i.test(fullText)) {
      issues.push({ type: 'placeholder', text: 'Placeholder content detected' });
    }

    // Check for very low content
    if (wordCount < 100) {
      issues.push({ type: 'low-content', wordCount });
    }

    // Check for repetition
    const words = fullText.toLowerCase().split(/\s+/);
    const wordFreq = {};
    words.forEach(word => {
      if (word.length > 4) {
        wordFreq[word] = (wordFreq[word] || 0) + 1;
      }
    });
    const repeatedWords = Object.entries(wordFreq)
      .filter(([word, count]) => count > 5)
      .map(([word, count]) => ({ word, count }));

    if (repeatedWords.length > 5) {
      issues.push({ type: 'repetition', words: repeatedWords });
    }

    return { metrics, issues, score: calculateScore(issues) };
  });

  console.log('Content Audit:', JSON.stringify(audit, null, 2));
});

function calculateScore(issues) {
  let score = 100;
  issues.forEach(issue => {
    switch (issue.type) {
      case 'placeholder': score -= 30; break;
      case 'ai-phrase': score -= 5; break;
      case 'low-content': score -= 15; break;
      case 'repetition': score -= 10; break;
    }
  });
  return Math.max(0, score);
}
```

---

## Content Quality Metrics

### Readability Analysis

```javascript
// Flesch Reading Ease (simplified)
function calculateReadability(text) {
  const words = text.split(/\s+/);
  const sentences = text.split(/[.!?]+/);
  const syllables = countSyllables(text);

  const avgWordsPerSentence = words.length / sentences.length;
  const avgSyllablesPerWord = syllables / words.length;

  const score = 206.835 - (1.015 * avgWordsPerSentence) - (84.6 * avgSyllablesPerWord);

  return {
    score: Math.round(score * 10) / 10,
    level: getReadabilityLevel(score),
    avgWordsPerSentence: Math.round(avgWordsPerSentence * 10) / 10,
    avgSyllablesPerWord: Math.round(avgSyllablesPerWord * 100) / 100,
  };
}

function getReadabilityLevel(score) {
  if (score >= 90) return 'Very Easy (5th grade)';
  if (score >= 80) return 'Easy (6th grade)';
  if (score >= 70) return 'Fairly Easy (7th grade)';
  if (score >= 60) return 'Standard (8th-9th grade)';
  if (score >= 50) return 'Fairly Difficult (10th-12th grade)';
  if (score >= 30) return 'Difficult (College)';
  return 'Very Difficult (Graduate)';
}

function countSyllables(text) {
  // Simplified syllable counter
  return text.toLowerCase()
    .replace(/[^a-z]/g, '')
    .replace(/[^aeiouy]+/g, ' ')
    .trim()
    .split(/\s+/)
    .length;
}
```

### Tone Consistency Check

```javascript
function analyzeToneConsistency(text) {
  const sections = text.split(/\n\n+/);

  const formalIndicators = [
    /\b(therefore|consequently|furthermore|moreover)\b/gi,
    /\b(shall|must|require|ensure)\b/gi,
    /\b(please be advised|kindly|respectfully)\b/gi,
  ];

  const casualIndicators = [
    /\b(awesome|cool|super|totally|basically)\b/gi,
    /\b(you guys|folks|hey|yo)\b/gi,
    /\b(gonna|wanna|kinda|sorta)\b/gi,
  ];

  const toneBySection = sections.map((section, index) => {
    let formalScore = 0;
    let casualScore = 0;

    formalIndicators.forEach(pattern => {
      const matches = section.match(pattern);
      if (matches) formalScore += matches.length;
    });

    casualIndicators.forEach(pattern => {
      const matches = section.match(pattern);
      if (matches) casualScore += matches.length;
    });

    return {
      section: index + 1,
      formalScore,
      casualScore,
      tone: formalScore > casualScore ? 'formal' : casualScore > formalScore ? 'casual' : 'neutral',
    };
  });

  // Check for inconsistency
  const tones = toneBySection.map(s => s.tone);
  const uniqueTones = [...new Set(tones)];
  const isConsistent = uniqueTones.length <= 2;

  return {
    isConsistent,
    dominantTone: getMostFrequent(tones),
    sections: toneBySection,
    recommendation: isConsistent
      ? 'Tone is consistent'
      : 'Consider reviewing tone consistency across sections',
  };
}
```

---

## Remediation Guide

### Fixing AI Slop

| Issue | Before | After |
|-------|--------|-------|
| Generic opener | "In today's digital age, businesses need solutions..." | "Marketing teams waste 20+ hours per week on manual reporting. Here's how to fix it." |
| Vague benefit | "Unlock the power of our platform" | "Reduce report generation time from 4 hours to 15 minutes" |
| Empty superlatives | "Our cutting-edge, revolutionary solution" | "Our solution processes 10,000 records per second with 99.9% accuracy" |
| Generic CTA | "Learn more" | "See how Acme Corp saved $50K using our platform" |

### Content Quality Checklist

```
[ ] No placeholder text (lorem ipsum, [your name], etc.)
[ ] First sentence hooks the reader with specific value
[ ] Benefits are quantified (numbers, percentages, time saved)
[ ] No more than 2 generic AI phrases per page
[ ] Tone is consistent throughout
[ ] CTAs are specific and contextual
[ ] No unfinished sections (TODO, TBD)
[ ] Real testimonials/proof points included
[ ] Unique value proposition is clear
[ ] Content answers "What's in it for me?"
```

### AI Content Review Process

1. **Generate** - Use AI for initial draft
2. **Humanize** - Add specific examples, data, personality
3. **Edit** - Remove generic phrases, add unique insights
4. **Review** - Check against detection patterns
5. **Finalize** - Ensure placeholder-free, consistent tone
