import { test, expect } from '@playwright/test';

test('tenant overpayment alert', async ({ page }) => {
  // Login as tenant
  await page.goto('http://localhost:8000/login');
  await page.fill('input[type="email"]', 'ahmad@example.com');
  await page.fill('input[type="password"]', 'password');
  await page.click('button[type="submit"]');

  // Go to payment page
  await page.goto('http://localhost:8000/tenant/payments');

  // Click report payment
  await page.click('button:has-text("Lapor Pembayaran")');

  // Select bill
  await page.selectOption('select[wire:model="bill_id"]', { index: 1 });

  // Fill amount more than bill (bill is 300000)
  await page.fill('input[wire:model.live="amount"]', '400000');

  // Wait for Livewire to update and show alert
  await page.waitForSelector('.alert-info:has-text("Jumlah yang Anda masukkan melebihi total tagihan")');

  await page.screenshot({ path: 'tenant_overpayment_alert.png', fullPage: true });

  // Submit payment to verify admin validation later
  await page.selectOption('select[wire:model="payment_method_id"]', { index: 1 });
  await page.setInputFiles('input[wire:model="evidence"]', {
    name: 'evidence.png',
    mimeType: 'image/png',
    buffer: Buffer.from('fake-image-content'),
  });
  await page.click('button:has-text("Simpan")');

  // Wait for modal to close
  await page.waitForSelector('.modal', { state: 'hidden' });
});
