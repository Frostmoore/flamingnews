/// Configurazione ambiente FlamingNews Mobile
library;

const String kApiBaseUrl = String.fromEnvironment(
  'API_BASE_URL',
  defaultValue: 'http://10.0.2.2:8000/api', // Android emulator → localhost
);
