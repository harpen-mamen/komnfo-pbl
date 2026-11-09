class AppConfig {
  // Untuk emulator Android (AVD) gunakan 10.0.2.2 yang akan merujuk ke localhost host machine
  // Untuk device fisik, gunakan IP komputer Anda (contoh: http://192.168.1.100:8000)
  static const apiBaseUrl = String.fromEnvironment(
    'SIPDT_API_BASE_URL',
    // Android emulator maps the host machine's localhost to 10.0.2.2.
    // Physical devices should override this with the computer's LAN IP.
    defaultValue: 'http://10.0.2.2:8000/api/v1',
  );

  static const apiTimeout = Duration(seconds: 20);

  // Nama aplikasi untuk tampilan
  static const appName = 'SIPDT Mobile';
  static const appVersion = '1.0.0';
  static const appDescription = 'Pendataan Terpadu Kabupaten Kepulauan Sangihe';
}
