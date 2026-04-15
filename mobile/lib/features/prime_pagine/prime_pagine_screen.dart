import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/api/api_client.dart';
import '../../shared/widgets/lean_badge.dart';

// ── Model ──────────────────────────────────────────────────────────────────

class PrimaPaginaItem {
  final int id;
  final String sourceName;
  final String sourceDomain;
  final String? politicalLean;
  final String imageUrl;
  final String? headline;
  final String? articleUrl;
  final DateTime? editionDate;

  const PrimaPaginaItem({
    required this.id,
    required this.sourceName,
    required this.sourceDomain,
    this.politicalLean,
    required this.imageUrl,
    this.headline,
    this.articleUrl,
    this.editionDate,
  });

  factory PrimaPaginaItem.fromJson(Map<String, dynamic> j) => PrimaPaginaItem(
        id:            j['id'] as int,
        sourceName:    j['source_name'] as String,
        sourceDomain:  j['source_domain'] as String,
        politicalLean: j['political_lean'] as String?,
        imageUrl:      j['image_url'] as String,
        headline:      j['headline'] as String?,
        articleUrl:    j['article_url'] as String?,
        editionDate:   j['edition_date'] != null
            ? DateTime.tryParse(j['edition_date'] as String)
            : null,
      );
}

// ── Provider ───────────────────────────────────────────────────────────────

final primePagineProvider = FutureProvider<List<PrimaPaginaItem>>((ref) async {
  final dio = ref.read(dioProvider);
  final res  = await dio.get('/prima-pagine');
  final data = (res.data['data'] as List<dynamic>);
  return data.map((e) => PrimaPaginaItem.fromJson(e as Map<String, dynamic>)).toList();
});

// ── Screen ─────────────────────────────────────────────────────────────────

class PrimePagineScreen extends ConsumerWidget {
  const PrimePagineScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final async = ref.watch(primePagineProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8F6F1),
      appBar: PreferredSize(
        preferredSize: const Size.fromHeight(4 + kToolbarHeight),
        child: Material(
          color: Colors.white,
          elevation: 1,
          shadowColor: Colors.black12,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(height: 4, color: const Color(0xFFC41E3A)),
              SizedBox(
                height: kToolbarHeight,
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  child: Row(
                    children: [
                      Text.rich(TextSpan(children: [
                        TextSpan(
                          text: 'Flaming',
                          style: GoogleFonts.playfairDisplay(
                            fontSize: 22, fontWeight: FontWeight.w800,
                            color: const Color(0xFF1A1A1A),
                          ),
                        ),
                        TextSpan(
                          text: 'News',
                          style: GoogleFonts.playfairDisplay(
                            fontSize: 22, fontWeight: FontWeight.w800,
                            color: const Color(0xFFC41E3A),
                          ),
                        ),
                      ])),
                      const SizedBox(width: 12),
                      const Text(
                        'Prime Pagine',
                        style: TextStyle(
                          fontSize: 13, color: Colors.black45,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const Spacer(),
                      async.whenOrNull(
                        data: (items) => items.isNotEmpty && items.first.editionDate != null
                            ? Text(
                                DateFormat('d MMM', 'it').format(items.first.editionDate!),
                                style: const TextStyle(fontSize: 11, color: Colors.grey),
                              )
                            : const SizedBox.shrink(),
                      ) ?? const SizedBox.shrink(),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
      body: async.when(
        loading: () => const Center(
          child: CircularProgressIndicator(color: Color(0xFFC41E3A), strokeWidth: 2),
        ),
        error: (e, _) => Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, color: Color(0xFFC41E3A), size: 40),
              const SizedBox(height: 12),
              Text(e.toString(), textAlign: TextAlign.center,
                  style: const TextStyle(color: Colors.black54)),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => ref.refresh(primePagineProvider),
                style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFC41E3A)),
                child: const Text('Riprova', style: TextStyle(color: Colors.white)),
              ),
            ],
          ),
        ),
        data: (items) {
          if (items.isEmpty) {
            return const Center(
              child: Text('Nessuna prima pagina disponibile.',
                  style: TextStyle(color: Colors.black45)),
            );
          }
          return GridView.builder(
            padding: const EdgeInsets.all(12),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              crossAxisSpacing: 10,
              mainAxisSpacing: 14,
              childAspectRatio: 0.62,
            ),
            itemCount: items.length,
            itemBuilder: (ctx, i) => _PrimaPaginaCard(item: items[i]),
          );
        },
      ),
    );
  }
}

// ── Card ───────────────────────────────────────────────────────────────────

class _PrimaPaginaCard extends StatelessWidget {
  final PrimaPaginaItem item;
  const _PrimaPaginaCard({required this.item});

  void _open() {
    final url = item.articleUrl;
    if (url != null) {
      launchUrl(Uri.parse(url), mode: LaunchMode.externalApplication);
    }
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: _open,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Immagine
          Expanded(
            child: ClipRRect(
              borderRadius: BorderRadius.zero,
              child: Stack(
                fit: StackFit.expand,
                children: [
                  CachedNetworkImage(
                    imageUrl: item.imageUrl,
                    fit: BoxFit.cover,
                    alignment: Alignment.topCenter,
                    errorWidget: (ctx, _, __) => Container(
                      color: Colors.grey.shade200,
                      child: const Icon(Icons.newspaper, color: Colors.grey, size: 40),
                    ),
                  ),
                  // Badge orientamento
                  if (item.politicalLean != null)
                    Positioned(
                      top: 6, left: 6,
                      child: LeanBadge(lean: item.politicalLean),
                    ),
                  // Overlay nome testata
                  Positioned(
                    bottom: 0, left: 0, right: 0,
                    child: Container(
                      padding: const EdgeInsets.fromLTRB(6, 16, 6, 6),
                      decoration: const BoxDecoration(
                        gradient: LinearGradient(
                          begin: Alignment.bottomCenter,
                          end: Alignment.topCenter,
                          colors: [Color(0xCC000000), Colors.transparent],
                        ),
                      ),
                      child: Text(
                        item.sourceName.toUpperCase(),
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 9,
                          fontWeight: FontWeight.w800,
                          letterSpacing: 0.6,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
          // Headline
          const SizedBox(height: 5),
          Text(
            item.headline ?? item.sourceName,
            style: const TextStyle(
              fontSize: 11,
              color: Color(0xFF374151),
              height: 1.3,
            ),
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }
}
