import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:google_mobile_ads/google_mobile_ads.dart';
import 'package:intl/date_symbol_data_local.dart';

import 'core/providers/auth_provider.dart';
import 'features/feed/feed_screen.dart';
import 'features/auth/login_screen.dart';
import 'features/auth/register_screen.dart';
import 'features/auth/categories_screen.dart';
import 'features/settings/settings_screen.dart';

late ProviderContainer _container;

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await initializeDateFormatting('it', null);

  await MobileAds.instance.initialize();

  _container = ProviderContainer();
  await _container.read(authProvider.notifier).restoreSession();

  runApp(UncontrolledProviderScope(
    container: _container,
    child: const FlamingNewsApp(),
  ));
}

// ---------------------------------------------------------------------------
// Router
// ---------------------------------------------------------------------------

final _router = GoRouter(
  refreshListenable: _RouterNotifier(),
  redirect: (context, state) {
    final auth = _container.read(authProvider);
    final isAuth = auth.isAuthenticated;
    final path = state.uri.path;

    final publicPaths = {'/login', '/register'};
    final isPublic = publicPaths.contains(path);

    if (!isAuth && !isPublic) return '/login';
    if (isAuth && isPublic) return '/';

    return null;
  },
  routes: [
    ShellRoute(
      builder: (ctx, state, child) => _Shell(child: child),
      routes: [
        GoRoute(path: '/',         builder: (ctx, _) => const FeedScreen()),
        GoRoute(path: '/settings', builder: (ctx, _) => const SettingsScreen()),
      ],
    ),
    GoRoute(path: '/login',      builder: (ctx, _) => const LoginScreen()),
    GoRoute(path: '/register',   builder: (ctx, _) => const RegisterScreen()),
    GoRoute(path: '/categories', builder: (ctx, _) => const CategoriesScreen()),
  ],
);

class _RouterNotifier extends ChangeNotifier {
  _RouterNotifier() {
    _container.listen(authProvider, (_, __) => notifyListeners());
  }
}

// ---------------------------------------------------------------------------
// App
// ---------------------------------------------------------------------------

class FlamingNewsApp extends StatelessWidget {
  const FlamingNewsApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp.router(
      title: 'FlamingNews',
      debugShowCheckedModeBanner: false,
      routerConfig: _router,
      theme: ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFFC41E3A),
          surface: const Color(0xFFF8F6F1),
        ),
        textTheme: GoogleFonts.sourceSans3TextTheme().copyWith(
          displayLarge: GoogleFonts.playfairDisplay(
            fontSize: 32, fontWeight: FontWeight.w700, color: const Color(0xFF1A1A1A),
          ),
          displayMedium: GoogleFonts.playfairDisplay(
            fontSize: 24, fontWeight: FontWeight.w700, color: const Color(0xFF1A1A1A),
          ),
          headlineMedium: GoogleFonts.playfairDisplay(
            fontSize: 18, fontWeight: FontWeight.w600, color: const Color(0xFF1A1A1A),
          ),
        ),
        appBarTheme: const AppBarTheme(
          backgroundColor: Colors.white,
          foregroundColor: Color(0xFF1A1A1A),
          elevation: 0,
          scrolledUnderElevation: 1,
        ),
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Shell con bottom navigation
// ---------------------------------------------------------------------------

class _Shell extends StatelessWidget {
  final Widget child;
  const _Shell({required this.child});

  @override
  Widget build(BuildContext context) {
    final location = GoRouterState.of(context).uri.path;
    final currentIndex = location.startsWith('/settings') ? 1 : 0;

    return Scaffold(
      body: child,
      bottomNavigationBar: NavigationBar(
        backgroundColor: Colors.white,
        indicatorColor: const Color(0xFFFFE4E8),
        selectedIndex: currentIndex,
        onDestinationSelected: (i) {
          if (i == 0) context.go('/');
          if (i == 1) context.go('/settings');
        },
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.newspaper_outlined),
            selectedIcon: Icon(Icons.newspaper),
            label: 'Feed',
          ),
          NavigationDestination(
            icon: Icon(Icons.person_outline),
            selectedIcon: Icon(Icons.person),
            label: 'Profilo',
          ),
        ],
      ),
    );
  }
}
