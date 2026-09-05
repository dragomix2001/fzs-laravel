/// <reference types="node" />

import { defineConfig, devices } from '@playwright/test';
import { env } from 'node:process';

/**
 * Playwright Configuration for FZS-Laravel Frontend Migration
 * 
 * Purpose: Browser-level regression coverage for the running application.
 */
export default defineConfig({
  testDir: './tests/e2e/specs',
  
  /* Run tests in files in parallel */
  fullyParallel: true,
  
  /* Fail the build on CI if you accidentally left test.only in the source code. */
  forbidOnly: !!env.CI,
  
  /* Retry on CI only */
  retries: env.CI ? 2 : 0,
  
  /* Opt out of parallel tests on CI. */
  workers: 1,
  
  /* Reporter to use. See https://playwright.dev/docs/test-reporters */
  reporter: [
    ['html', { outputFolder: 'playwright-report' }],
    ['list'],
  ],
  
  /* Shared settings for all the projects below. See https://playwright.dev/docs/api/class-testoptions. */
  use: {
    /* Base URL to use in actions like `await page.goto('/')`. */
    baseURL: env.E2E_BASE_URL ?? 'http://localhost:8080',
    
    /* Collect trace when retrying the failed test. See https://playwright.dev/docs/trace-viewer */
    trace: 'on-first-retry',
    
    /* Screenshot on failure only (saves space) */
    screenshot: 'only-on-failure',
    
    /* Video on failure only */
    video: 'retain-on-failure',
    
    /* Ignore HTTPS errors (for local dev) */
    ignoreHTTPSErrors: true,
  },

  /* Visual regression settings */
  expect: {
    toHaveScreenshot: {
      // Allow 20% pixel difference (accounts for font rendering, anti-aliasing)
      threshold: 0.2,
      
      // Max 100 pixels can differ
      maxDiffPixels: 100,
      
      // Disable animations for stable screenshots
      animations: 'disabled',
      
      // Wait for fonts to load
      scale: 'css',
      
    },
  },

  /* Increase default test timeout */
  timeout: 90000,

  /* Configure projects for major browsers */
  projects: [
    {
      name: 'setup',
      testMatch: /auth\.setup\.ts/,
    },
    {
      name: 'chromium',
      dependencies: ['setup'],
      use: {
        ...devices['Desktop Chrome'],
        storageState: 'playwright/.auth/admin.json',
      },
    },
  ],

  /* The Docker stack is the default local test server. Set E2E_START_SERVER=true
   * when a standalone PHP server should be started instead. */
  webServer: env.E2E_START_SERVER === 'true' ? {
    command: 'php artisan serve --host=0.0.0.0 --port=8000',
    url: 'http://localhost:8000',
    reuseExistingServer: !process.env.CI,
    timeout: 120 * 1000,
  } : undefined,
});
