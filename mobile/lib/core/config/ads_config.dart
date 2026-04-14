import 'dart:io';

/// Configurazione Google AdMob.
///
/// In sviluppo vengono usati gli ID di test ufficiali Google.
/// In produzione sostituisci con i tuoi ID reali presi dalla console AdMob.
class AdsConfig {
  AdsConfig._();

  // ── App IDs (da inserire anche nei manifest nativi) ──────────────────────
  // Android: AndroidManifest.xml  → com.google.android.gms.ads.APPLICATION_ID
  // iOS:     Info.plist           → GADApplicationIdentifier
  static const String _testAndroidAppId = 'ca-app-pub-3940256099942544~3347511713';
  static const String _testIosAppId     = 'ca-app-pub-3940256099942544~1458002511';

  // Sostituisci con i tuoi:
  static const String _prodAndroidAppId = '';   // es. ca-app-pub-XXXXXXXXXXXXXXXX~XXXXXXXXXX
  static const String _prodIosAppId     = '';   // es. ca-app-pub-XXXXXXXXXXXXXXXX~XXXXXXXXXX

  // ── Ad unit IDs ──────────────────────────────────────────────────────────
  static const String _testBannerAndroid = 'ca-app-pub-3940256099942544/6300978111';
  static const String _testBannerIos     = 'ca-app-pub-3940256099942544/2934735716';

  // Sostituisci con i tuoi:
  static const String _prodBannerAndroid = '';   // es. ca-app-pub-XXXXXXXXXXXXXXXX/XXXXXXXXXX
  static const String _prodBannerIos     = '';   // es. ca-app-pub-XXXXXXXXXXXXXXXX/XXXXXXXXXX

  // ── Selettori pubblici ───────────────────────────────────────────────────
  static bool get _isProd =>
      _prodBannerAndroid.isNotEmpty && _prodBannerIos.isNotEmpty;

  static String get appId => Platform.isIOS
      ? (_isProd ? _prodIosAppId     : _testIosAppId)
      : (_isProd ? _prodAndroidAppId : _testAndroidAppId);

  static String get bannerAdUnitId => Platform.isIOS
      ? (_isProd ? _prodBannerIos     : _testBannerIos)
      : (_isProd ? _prodBannerAndroid : _testBannerAndroid);

  /// Inserisce un banner ogni N articoli nel feed
  static const int feedAdFrequency = 6;
}
