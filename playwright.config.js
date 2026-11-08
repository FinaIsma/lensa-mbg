import { defineConfig, devices } from '@playwright/test';

/**
 * Konfigurasi Playwright untuk Integration / E2E Testing Lensa MBG
 * @see https://playwright.dev/docs/test-configuration
 */
export default defineConfig({
  testDir: './tests/e2e',
  timeout: 35 * 1000,
  expect: {
    timeout: 7000,
  },
  fullyParallel: false,
  retries: 0,
  workers: 1,
  reporter: [['list'], ['html', { open: 'never' }]],
  use: {
    // Sesuaikan URL aplikasi lokal Anda (default: http://localhost:8000)
    baseURL: process.env.APP_URL || 'http://localhost:8000',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
