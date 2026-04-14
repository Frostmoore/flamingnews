import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/providers/topics_provider.dart';
import '../../core/providers/auth_provider.dart';
import '../../core/api/api_client.dart';
import 'source_card.dart';

class CoverageDetailScreen extends ConsumerStatefulWidget {
  final int topicId;
  const CoverageDetailScreen({super.key, required this.topicId});

  @override
  ConsumerState<CoverageDetailScreen> createState() => _CoverageDetailScreenState();
}

class _CoverageDetailScreenState extends ConsumerState<CoverageDetailScreen> {
  bool _isAnalyzing = false;

  Future<void> _generateAnalysis() async {
    setState(() => _isAnalyzing = true);
    try {
      final dio = ref.read(dioProvider);
      final response = await dio.post('/topics/${widget.topicId}/analyze');
      final data = response.data as Map<String, dynamic>;
      ref.read(topicAnalysisProvider(widget.topicId).notifier).state =
          data['ai_analysis'] as String?;
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Errore nella generazione dell\'analisi AI.')),
        );
      }
    } finally {
      if (mounted) setState(() => _isAnalyzing = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final topicAsync = ref.watch(topicDetailProvider(widget.topicId));
    final auth = ref.watch(authProvider);
    final localAnalysis = ref.watch(topicAnalysisProvider(widget.topicId));

    return Scaffold(
      backgroundColor: const Color(0xFFF8F6F1),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: Color(0xFF1A1A1A)),
        title: const Text('Coverage',
            style: TextStyle(color: Color(0xFF1A1A1A), fontSize: 16, fontWeight: FontWeight.w700)),
      ),
      body: topicAsync.when(
        loading: () => const Center(child: CircularProgressIndicator(color: Color(0xFFC41E3A))),
        error: (e, _) => Center(child: Text('Errore: $e')),
        data: (topic) {
          final analysis = localAnalysis ?? topic.aiAnalysis;
          const leanOrder = ['left', 'center', 'right', 'international'];

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              // Titolo
              Text(topic.title,
                style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: Color(0xFF1A1A1A), height: 1.3)),
              const SizedBox(height: 4),
              Text('${topic.articleCount} fonti',
                style: const TextStyle(fontSize: 12, color: Colors.grey)),

              // Keywords
              if (topic.keywords.isNotEmpty) ...[
                const SizedBox(height: 10),
                Wrap(
                  spacing: 4, runSpacing: 4,
                  children: topic.keywords.map((kw) => Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(color: Colors.grey.shade100, borderRadius: BorderRadius.circular(12)),
                    child: Text(kw, style: const TextStyle(fontSize: 11, color: Colors.black54)),
                  )).toList(),
                ),
              ],

              const SizedBox(height: 20),

              // Griglia fonti per orientamento (scroll orizzontale)
              ...leanOrder.map((lean) {
                final articles = topic.sources[lean];
                if (articles == null || articles.isEmpty) return const SizedBox.shrink();
                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _LeanHeader(lean: lean),
                    const SizedBox(height: 8),
                    SizedBox(
                      height: 180,
                      child: ListView.builder(
                        scrollDirection: Axis.horizontal,
                        itemCount: articles.length,
                        itemBuilder: (ctx, i) => SourceCard(article: articles[i], lean: lean),
                      ),
                    ),
                    const SizedBox(height: 20),
                  ],
                );
              }),

              // Pannello AI
              if (auth.isPremium) ...[
                const Divider(),
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFFBEB),
                    border: Border.all(color: const Color(0xFFFCD34D)),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(color: const Color(0xFFFEF3C7), borderRadius: BorderRadius.circular(4)),
                          child: const Text('ANALISI AI', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: Color(0xFFB45309))),
                        ),
                      ]),
                      const SizedBox(height: 10),
                      if (analysis != null) ...[
                        Text(analysis, style: const TextStyle(fontSize: 14, color: Color(0xFF1A1A1A), height: 1.6)),
                      ] else if (_isAnalyzing) ...[
                        const Row(children: [
                          SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFFB45309))),
                          SizedBox(width: 10),
                          Text('Analisi in corso con Claude AI...', style: TextStyle(fontSize: 13, color: Color(0xFFB45309))),
                        ]),
                      ] else ...[
                        const Text('Genera un\'analisi comparativa su come le diverse testate raccontano questo evento.',
                          style: TextStyle(fontSize: 13, color: Color(0xFF92400E))),
                        const SizedBox(height: 10),
                        ElevatedButton(
                          onPressed: _generateAnalysis,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFFB45309),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                          ),
                          child: const Text('Genera analisi', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                        ),
                      ],
                      const SizedBox(height: 8),
                      const Text('Analisi generata da Claude AI (Anthropic). Solo scopo informativo.',
                        style: TextStyle(fontSize: 10, color: Color(0xFFB45309))),
                    ],
                  ),
                ),
              ] else ...[
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    border: Border.all(color: Colors.grey.shade300, style: BorderStyle.solid),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Text(
                    'L\'analisi AI è disponibile per gli utenti Premium.',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 13, color: Colors.black54),
                  ),
                ),
              ],

              const SizedBox(height: 32),
            ],
          );
        },
      ),
    );
  }
}

class _LeanHeader extends StatelessWidget {
  final String lean;
  const _LeanHeader({required this.lean});

  static const _labels = {
    'left': 'Sinistra',
    'right': 'Destra',
    'center': 'Centro',
    'international': 'Internazionale',
  };

  static const _colors = {
    'left':          Color(0xFF2563EB),
    'right':         Color(0xFFDC2626),
    'center':        Color(0xFF6B7280),
    'international': Color(0xFFD97706),
  };

  @override
  Widget build(BuildContext context) {
    return Row(children: [
      Container(width: 4, height: 16, color: _colors[lean] ?? Colors.grey),
      const SizedBox(width: 8),
      Text(
        (_labels[lean] ?? lean).toUpperCase(),
        style: TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w800,
          color: _colors[lean] ?? Colors.grey,
          letterSpacing: 1,
        ),
      ),
    ]);
  }
}
