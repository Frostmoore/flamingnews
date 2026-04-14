import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_sign_in/google_sign_in.dart';
import '../api/api_client.dart';
import '../models/user.dart';

final _googleSignIn = GoogleSignIn(scopes: ['openid', 'profile', 'email']);

class AuthState {
  final User? user;
  final bool loading;
  final String? error;

  const AuthState({this.user, this.loading = false, this.error});

  bool get isAuthenticated => user != null;
  bool get isPremium => user?.isPremium ?? false;
  bool get needsCategories => user?.needsCategories ?? false;

  AuthState copyWith({User? user, bool? loading, String? error}) => AuthState(
        user: user ?? this.user,
        loading: loading ?? this.loading,
        error: error,
      );
}

class AuthNotifier extends StateNotifier<AuthState> {
  final Ref _ref;

  AuthNotifier(this._ref) : super(const AuthState());

  // ---------------------------------------------------------------------------
  // Ripristino sessione all'avvio
  // ---------------------------------------------------------------------------

  Future<void> restoreSession() async {
    final token = await getAuthToken();
    if (token == null) return;

    try {
      final dio = _ref.read(dioProvider);
      final response = await dio.get('/auth/me');
      final data = response.data as Map<String, dynamic>;
      state = state.copyWith(user: User.fromJson(data['user'] as Map<String, dynamic>));
    } catch (_) {
      await clearAuthToken(); // token scaduto
    }
  }

  // ---------------------------------------------------------------------------
  // Login email/password
  // ---------------------------------------------------------------------------

  Future<bool> login(String email, String password) async {
    state = state.copyWith(loading: true, error: null);
    try {
      final dio = _ref.read(dioProvider);
      final response = await dio.post('/auth/login', data: {'email': email, 'password': password});
      final data = response.data as Map<String, dynamic>;
      await saveAuthToken(data['token'] as String);
      state = state.copyWith(
        user: User.fromJson(data['user'] as Map<String, dynamic>),
        loading: false,
      );
      return true;
    } catch (e) {
      state = state.copyWith(loading: false, error: 'Credenziali non valide.');
      return false;
    }
  }

  // ---------------------------------------------------------------------------
  // Registrazione email/password + categorie
  // ---------------------------------------------------------------------------

  Future<bool> register(
    String name,
    String email,
    String password,
    List<String> preferredCategories,
  ) async {
    state = state.copyWith(loading: true, error: null);
    try {
      final dio = _ref.read(dioProvider);
      final response = await dio.post('/auth/register', data: {
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': password,
        'preferred_categories': preferredCategories,
      });
      final data = response.data as Map<String, dynamic>;
      await saveAuthToken(data['token'] as String);
      state = state.copyWith(
        user: User.fromJson(data['user'] as Map<String, dynamic>),
        loading: false,
      );
      return true;
    } catch (e) {
      state = state.copyWith(loading: false, error: 'Errore nella registrazione.');
      return false;
    }
  }

  // ---------------------------------------------------------------------------
  // Google Sign-In
  // ---------------------------------------------------------------------------

  Future<bool> loginWithGoogle() async {
    state = state.copyWith(loading: true, error: null);
    try {
      final googleAccount = await _googleSignIn.signIn();
      if (googleAccount == null) {
        // Utente ha annullato
        state = state.copyWith(loading: false);
        return false;
      }

      final auth    = await googleAccount.authentication;
      final idToken = auth.idToken;
      if (idToken == null) throw Exception('Impossibile ottenere il token Google.');

      final dio = _ref.read(dioProvider);
      final response = await dio.post('/auth/google/mobile', data: {'id_token': idToken});
      final data = response.data as Map<String, dynamic>;

      await saveAuthToken(data['token'] as String);
      state = state.copyWith(
        user: User.fromJson(data['user'] as Map<String, dynamic>),
        loading: false,
      );
      return true;
    } catch (e) {
      await _googleSignIn.signOut();
      state = state.copyWith(loading: false, error: 'Accesso con Google fallito.');
      return false;
    }
  }

  // ---------------------------------------------------------------------------
  // Salva categorie preferite
  // ---------------------------------------------------------------------------

  Future<bool> updateCategories(List<String> categories) async {
    state = state.copyWith(loading: true, error: null);
    try {
      final dio = _ref.read(dioProvider);
      final response = await dio.patch('/auth/categories', data: {'preferred_categories': categories});
      final data = response.data as Map<String, dynamic>;
      state = state.copyWith(
        user: User.fromJson(data['user'] as Map<String, dynamic>),
        loading: false,
      );
      return true;
    } catch (e) {
      state = state.copyWith(loading: false, error: 'Errore nel salvataggio delle categorie.');
      return false;
    }
  }

  // ---------------------------------------------------------------------------
  // Logout
  // ---------------------------------------------------------------------------

  Future<void> logout() async {
    try {
      final dio = _ref.read(dioProvider);
      await dio.post('/auth/logout');
      await _googleSignIn.signOut();
    } finally {
      await clearAuthToken();
      state = const AuthState();
    }
  }
}

final authProvider = StateNotifierProvider<AuthNotifier, AuthState>(
  (ref) => AuthNotifier(ref),
);
