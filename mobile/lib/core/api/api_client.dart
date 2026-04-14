import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../config/env.dart';

final _storage = FlutterSecureStorage();

final dioProvider = Provider<Dio>((ref) {
  final dio = Dio(BaseOptions(
    baseUrl: kApiBaseUrl,
    connectTimeout: const Duration(seconds: 10),
    receiveTimeout: const Duration(seconds: 15),
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
  ));

  // Interceptor: aggiunge Bearer token se presente
  dio.interceptors.add(
    InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token = await _storage.read(key: 'fn_token');
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        handler.next(options);
      },
      onError: (error, handler) {
        handler.next(error);
      },
    ),
  );

  return dio;
});

/// Helper per salvare il token di autenticazione
Future<void> saveAuthToken(String token) async {
  await _storage.write(key: 'fn_token', value: token);
}

/// Helper per leggere il token corrente
Future<String?> getAuthToken() async {
  return _storage.read(key: 'fn_token');
}

/// Helper per cancellare il token
Future<void> clearAuthToken() async {
  await _storage.delete(key: 'fn_token');
}
