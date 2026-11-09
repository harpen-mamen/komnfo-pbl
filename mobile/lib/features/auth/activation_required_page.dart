import 'package:flutter/material.dart';

import '../../core/session_controller.dart';

/// Halaman aktivasi perangkat dengan tema Sangihe.
class ActivationRequiredPage extends StatelessWidget {
  const ActivationRequiredPage({super.key, required this.session});

  final SessionController session;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 440),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Container(
                    padding: const EdgeInsets.all(24),
                    decoration: BoxDecoration(
                      color: const Color(0xff0b3c4d).withValues(alpha: 0.1),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.phonelink_lock_rounded,
                      size: 64,
                      color: Color(0xff0b3c4d),
                    ),
                  ),
                  const SizedBox(height: 24),
                  Text(
                    'Perangkat Belum Diaktivasi',
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                          fontWeight: FontWeight.w800,
                          color: colors.onSurface,
                        ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    session.activationError ??
                        'ID Perangkat mobile ini belum terdaftar untuk akun Admin Desa di server Kabupaten Kepulauan Sangihe.',
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          color: Colors.grey.shade700,
                          height: 1.5,
                        ),
                  ),
                  const SizedBox(height: 28),
                  Container(
                    padding: const EdgeInsets.all(18),
                    decoration: BoxDecoration(
                      color: const Color(0xffebf1f0),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: const Color(0xffd5e0df)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Row(
                          children: [
                            Icon(Icons.verified_user_rounded, color: Color(0xff0b3c4d), size: 20),
                            SizedBox(width: 8),
                            Text(
                              'Panduan Aktivasi Petugas',
                              style: TextStyle(
                                fontWeight: FontWeight.w800,
                                color: Color(0xff0b3c4d),
                                fontSize: 14,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        Text(
                          '1. Hubungi Admin OPD / Kecamatan Anda\n'
                          '2. Informasikan nama akun Admin Desa\n'
                          '3. Admin OPD mengaktifkan Device ID via Web Admin Panel\n'
                          '4. Setelah diaktifkan, tekan tombol di bawah',
                          style: TextStyle(
                            fontSize: 13,
                            color: Colors.grey.shade800,
                            height: 1.6,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 28),
                  FilledButton.icon(
                    onPressed: session.isLoading ? null : () => session.retryAutoLogin(),
                    icon: session.isLoading
                        ? const SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.white,
                            ),
                          )
                        : const Icon(Icons.refresh_rounded),
                    label: Text(
                      session.isLoading ? 'Memeriksa Aktivasi...' : 'Saya Sudah Diaktifkan, Coba Lagi',
                    ),
                    style: FilledButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 18),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
