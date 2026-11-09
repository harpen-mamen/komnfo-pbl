import 'package:flutter/material.dart';

import '../../core/session_controller.dart';

class ConnectionErrorPage extends StatelessWidget {
  const ConnectionErrorPage({super.key, required this.session, required this.kind});

  final SessionController session;
  final ConnectionErrorKind kind;

  @override
  Widget build(BuildContext context) {
    final config = _configFor(kind);

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
                      color: config.iconColor.withValues(alpha: 0.12),
                      shape: BoxShape.circle,
                    ),
                    child: Icon(
                      config.icon,
                      size: 64,
                      color: config.iconColor,
                    ),
                  ),
                  const SizedBox(height: 24),
                  Text(
                    config.title,
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.w800,
                      color: config.titleColor,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    session.connectionError ?? config.defaultMessage,
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 14,
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
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Icon(Icons.lightbulb_outline_rounded, color: Color(0xff0b3c4d), size: 22),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Text(
                            config.tip,
                            style: const TextStyle(
                              fontSize: 13,
                              color: Color(0xff0b3c4d),
                              height: 1.5,
                              fontWeight: FontWeight.w500,
                            ),
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
                      session.isLoading ? 'Memeriksa Koneksi...' : config.buttonLabel,
                    ),
                    style: FilledButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 18),
                    ),
                  ),
                  const SizedBox(height: 12),
                  OutlinedButton.icon(
                    onPressed: () => session.enterOfflineMode(),
                    icon: const Icon(Icons.smartphone_rounded, size: 18),
                    label: const Text('Lanjut Dalam Mode Offline'),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
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

  _ErrorConfig _configFor(ConnectionErrorKind k) {
    switch (k) {
      case ConnectionErrorKind.noConnection:
        return const _ErrorConfig(
          icon: Icons.wifi_off_rounded,
          iconColor: Color(0xffd4af37),
          title: 'Jaringan Terputus',
          titleColor: Color(0xff3b2e04),
          defaultMessage: 'Perangkat tidak dapat terhubung ke server SIPDT Sangihe.',
          tip: 'Petunjuk:\n'
              '• Periksa koneksi seluler / WiFi di lokasi Anda\n'
              '• Tetap dapat mengisi data dalam Mode Offline\n'
              '• Draft lokal otomatis aman tersimpan',
          buttonLabel: 'Coba Hubungkan Kembali',
        );
      case ConnectionErrorKind.serverError:
        return const _ErrorConfig(
          icon: Icons.cloud_off_rounded,
          iconColor: Color(0xffa4463b),
          title: 'Server SIPDT Gangguan',
          titleColor: Color(0xff6f2926),
          defaultMessage: 'Layanan server SIPDT sedang dalam pemeliharaan.',
          tip: 'Petunjuk:\n'
              '• Cobalah beberapa saat lagi\n'
              '• Anda dapat melanjutkan pendataan dalam Mode Offline\n'
              '• Semua draft akan dikirim saat server aktif kembali',
          buttonLabel: 'Coba Lagi',
        );
    }
  }
}

enum ConnectionErrorKind { noConnection, serverError }

class _ErrorConfig {
  const _ErrorConfig({
    required this.icon,
    required this.iconColor,
    required this.title,
    required this.titleColor,
    required this.defaultMessage,
    required this.tip,
    required this.buttonLabel,
  });

  final IconData icon;
  final Color iconColor;
  final String title;
  final Color titleColor;
  final String defaultMessage;
  final String tip;
  final String buttonLabel;
}
