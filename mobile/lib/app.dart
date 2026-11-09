import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import 'core/api_client.dart';
import 'core/config.dart';
import 'core/local_store.dart';
import 'core/session_controller.dart';
import 'features/auth/activation_required_page.dart';
import 'features/auth/connection_error_page.dart';
import 'features/home/home_page.dart';

/// Root app dengan Tema Khas Pemerintah Kabupaten Kepulauan Sangihe.
/// Warna Utama: Ocean Maritime Teal (#0B3C4D), Gold Crest (#D4AF37), Seafoam Neutral (#F4F7F6).
class SipdtApp extends StatefulWidget {
  const SipdtApp({
    super.key,
    required this.session,
    required this.api,
    required this.store,
  });

  final SessionController session;
  final ApiClient api;
  final LocalStore store;

  @override
  State<SipdtApp> createState() => _SipdtAppState();
}

class _SipdtAppState extends State<SipdtApp> {
  @override
  void initState() {
    super.initState();
    _applySystemUi();
  }

  void _applySystemUi() {
    SystemChrome.setSystemUIOverlayStyle(
      const SystemUiOverlayStyle(
        statusBarColor: Colors.transparent,
        statusBarIconBrightness: Brightness.light,
        statusBarBrightness: Brightness.dark,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: widget.session,
      builder: (context, _) {
        return MaterialApp(
          title: AppConfig.appName,
          debugShowCheckedModeBanner: false,
          theme: _buildTheme(),
          home: _resolveHome(),
        );
      },
    );
  }

  Widget _resolveHome() {
    final session = widget.session;
    final api = widget.api;
    final store = widget.store;

    if (session.isLoading) {
      return const _SplashLoadingPage();
    }

    if (session.offlineMode || session.isAuthenticated) {
      return HomePage(session: session, api: api, store: store);
    }

    if (session.needsActivation) {
      return ActivationRequiredPage(session: session);
    }

    if (session.noConnection) {
      return ConnectionErrorPage(
        session: session,
        kind: ConnectionErrorKind.noConnection,
      );
    }

    if (session.serverError) {
      return ConnectionErrorPage(
        session: session,
        kind: ConnectionErrorKind.serverError,
      );
    }

    return const _SplashLoadingPage();
  }

  ThemeData _buildTheme() {
    // Sangihe Brand Palette:
    // Primary: Deep Ocean Teal Sangihe (0xff0b3c4d)
    // Accent: Emas Mahkota Sangihe (0xffd4af37)
    // Secondary Info: Samudra Blue (0xff1f5f77)
    const seedColor = Color(0xff0b3c4d);
    const accentColor = Color(0xffd4af37);
    const infoColor = Color(0xff1f5f77);

    final colorScheme = ColorScheme.fromSeed(
      seedColor: seedColor,
      brightness: Brightness.light,
    ).copyWith(
      primary: seedColor,
      secondary: accentColor,
      tertiary: infoColor,
      onPrimary: Colors.white,
      surface: const Color(0xfff4f7f6),
      onSurface: const Color(0xff0e2129),
      onSurfaceVariant: const Color(0xff4f636b),
      surfaceTint: Colors.transparent,
      surfaceContainerLowest: Colors.white,
      surfaceContainerLow: const Color(0xffebf1f0),
      surfaceContainer: const Color(0xffe1e9e8),
      surfaceContainerHigh: const Color(0xffd5e0df),
      outline: const Color(0xffa1b5b3),
      outlineVariant: const Color(0xffd2dedc),
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: colorScheme,
      scaffoldBackgroundColor: const Color(0xfff4f7f6),
      textTheme: TextTheme(
        displaySmall: TextStyle(fontSize: 28, fontWeight: FontWeight.w800, color: colorScheme.onSurface, height: 1.2, letterSpacing: -0.4),
        headlineLarge: TextStyle(fontSize: 24, fontWeight: FontWeight.w700, color: colorScheme.onSurface, height: 1.25, letterSpacing: -0.3),
        headlineMedium: TextStyle(fontSize: 20, fontWeight: FontWeight.w700, color: colorScheme.onSurface, height: 1.3),
        headlineSmall: TextStyle(fontSize: 18, fontWeight: FontWeight.w600, color: colorScheme.onSurface, height: 1.35),
        titleLarge: TextStyle(fontSize: 17, fontWeight: FontWeight.w600, color: colorScheme.onSurface, height: 1.4),
        titleMedium: TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: colorScheme.onSurface, height: 1.4),
        titleSmall: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: colorScheme.onSurface, height: 1.4),
        bodyLarge: TextStyle(fontSize: 15, fontWeight: FontWeight.w400, color: colorScheme.onSurface, height: 1.5),
        bodyMedium: TextStyle(fontSize: 14, fontWeight: FontWeight.w400, color: colorScheme.onSurface, height: 1.5),
        bodySmall: TextStyle(fontSize: 12, fontWeight: FontWeight.w400, color: colorScheme.onSurfaceVariant, height: 1.45),
        labelLarge: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: colorScheme.onSurface, letterSpacing: 0.1),
        labelMedium: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: colorScheme.onSurface, letterSpacing: 0.2),
        labelSmall: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: colorScheme.onSurfaceVariant, letterSpacing: 0.5),
      ),
      appBarTheme: AppBarTheme(
        centerTitle: false,
        elevation: 0,
        scrolledUnderElevation: 2,
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
        foregroundColor: colorScheme.onSurface,
        titleTextStyle: TextStyle(fontSize: 19, fontWeight: FontWeight.w700, color: colorScheme.onSurface, letterSpacing: -0.2),
        iconTheme: IconThemeData(color: colorScheme.onSurface),
        actionsIconTheme: IconThemeData(color: colorScheme.onSurface),
      ),
      cardTheme: CardThemeData(
        elevation: 0,
        color: Colors.white,
        margin: EdgeInsets.zero,
        clipBehavior: Clip.antiAlias,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(20),
          side: BorderSide(color: colorScheme.outlineVariant, width: 1),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: const Color(0xfff8faf9),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide(color: colorScheme.outlineVariant),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide(color: colorScheme.primary, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide(color: colorScheme.error, width: 1),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide(color: colorScheme.error, width: 2),
        ),
        contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
        labelStyle: TextStyle(color: Colors.grey.shade700, fontWeight: FontWeight.w500),
        hintStyle: TextStyle(color: Colors.grey.shade400),
        helperStyle: TextStyle(color: Colors.grey.shade600, fontSize: 12),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: colorScheme.primary,
          foregroundColor: Colors.white,
          elevation: 2,
          shadowColor: seedColor.withValues(alpha: 0.3),
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          textStyle: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, letterSpacing: 0.2),
        ),
      ),
      progressIndicatorTheme: const ProgressIndicatorThemeData(color: seedColor),
      dividerTheme: const DividerThemeData(color: Color(0xffd2dedc), thickness: 1, space: 1),
      listTileTheme: const ListTileThemeData(iconColor: seedColor, textColor: Color(0xff0e2129)),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: colorScheme.primary,
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          side: BorderSide(color: colorScheme.primary.withValues(alpha: 0.4), width: 1.5),
          textStyle: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, letterSpacing: 0.2),
        ),
      ),
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: colorScheme.primary,
          textStyle: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700),
        ),
      ),
      floatingActionButtonTheme: FloatingActionButtonThemeData(
        backgroundColor: colorScheme.primary,
        foregroundColor: Colors.white,
        elevation: 4,
        highlightElevation: 8,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        extendedTextStyle: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, letterSpacing: 0.3),
      ),
      navigationBarTheme: NavigationBarThemeData(
        backgroundColor: Colors.white,
        elevation: 10,
        shadowColor: Colors.black.withValues(alpha: 0.08),
        height: 72,
        labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
        indicatorColor: colorScheme.primary.withValues(alpha: 0.12),
        surfaceTintColor: Colors.transparent,
        iconTheme: WidgetStateProperty.resolveWith((states) {
          if (states.contains(WidgetState.selected)) {
            return IconThemeData(color: colorScheme.primary, size: 24);
          }
          return IconThemeData(color: Colors.grey.shade500, size: 24);
        }),
        labelTextStyle: WidgetStateProperty.resolveWith((states) {
          final color = states.contains(WidgetState.selected) ? colorScheme.primary : Colors.grey.shade600;
          final weight = states.contains(WidgetState.selected) ? FontWeight.w700 : FontWeight.w500;
          return TextStyle(fontSize: 11, fontWeight: weight, color: color);
        }),
      ),
      chipTheme: ChipThemeData(
        backgroundColor: Colors.grey.shade100,
        selectedColor: colorScheme.primary.withValues(alpha: 0.12),
        labelStyle: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Colors.grey.shade800),
        secondaryLabelStyle: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: colorScheme.primary),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        side: BorderSide.none,
        checkmarkColor: colorScheme.primary,
      ),
      snackBarTheme: SnackBarThemeData(
        behavior: SnackBarBehavior.floating,
        backgroundColor: colorScheme.inverseSurface,
        contentTextStyle: TextStyle(color: colorScheme.onInverseSurface, fontSize: 14, fontWeight: FontWeight.w500),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        elevation: 6,
      ),
      pageTransitionsTheme: const PageTransitionsTheme(
        builders: {
          TargetPlatform.android: PredictiveBackPageTransitionsBuilder(),
          TargetPlatform.iOS: CupertinoPageTransitionsBuilder(),
        },
      ),
    );
  }
}

/// Halaman splash dengan animasi bergelombang dan logo emas Sangihe.
class _SplashLoadingPage extends StatefulWidget {
  const _SplashLoadingPage();

  @override
  State<_SplashLoadingPage> createState() => _SplashLoadingPageState();
}

class _SplashLoadingPageState extends State<_SplashLoadingPage> with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  late final Animation<double> _scale;
  late final Animation<double> _pulse;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1800),
    )..repeat(reverse: true);

    _scale = Tween<double>(begin: 0.95, end: 1.05).animate(
      CurvedAnimation(parent: _ctrl, curve: Curves.easeInOut),
    );

    _pulse = Tween<double>(begin: 0.3, end: 0.7).animate(
      CurvedAnimation(parent: _ctrl, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xff06232d),
      body: Stack(
        children: [
          // Background Gradient Ocean Wave
          const Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [
                  Color(0xff0b3c4d),
                  Color(0xff06232d),
                  Color(0xff02131a),
                ],
              ),
            ),
          ),
          Center(
            child: AnimatedBuilder(
              animation: _ctrl,
              builder: (context, _) {
                return Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    // Outer pulsing ring
                    Stack(
                      alignment: Alignment.center,
                      children: [
                        Container(
                          width: 128 * _scale.value,
                          height: 128 * _scale.value,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: const Color(0xffd4af37).withValues(alpha: _pulse.value * 0.25),
                          ),
                        ),
                        Container(
                          width: 96,
                          height: 96,
                          decoration: BoxDecoration(
                            color: const Color(0xff0b3c4d),
                            shape: BoxShape.circle,
                            border: Border.all(color: const Color(0xffd4af37), width: 2),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xffd4af37).withValues(alpha: 0.3),
                                blurRadius: 20,
                                spreadRadius: 2,
                              )
                            ],
                          ),
                          child: const Icon(
                            Icons.landscape_rounded,
                            size: 48,
                            color: Color(0xffd4af37),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 32),
                    const Text(
                      'SIPDT Mobile',
                      style: TextStyle(
                        fontSize: 26,
                        fontWeight: FontWeight.w800,
                        color: Colors.white,
                        letterSpacing: 0.5,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                      decoration: BoxDecoration(
                        color: const Color(0xffd4af37).withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xffd4af37).withValues(alpha: 0.4)),
                      ),
                      child: const Text(
                        'Pemerintah Kabupaten Kepulauan Sangihe',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: Color(0xffe2c46c),
                          letterSpacing: 0.3,
                        ),
                      ),
                    ),
                    const SizedBox(height: 48),
                    SizedBox(
                      width: 140,
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(4),
                        child: const LinearProgressIndicator(
                          minHeight: 4,
                          backgroundColor: Color(0xff124252),
                          valueColor: AlwaysStoppedAnimation<Color>(Color(0xffd4af37)),
                        ),
                      ),
                    ),
                  ],
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
