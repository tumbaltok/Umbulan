import 'package:material_ui/material_ui.dart';
import '../../../core/models/app_models.dart';
import '../../../core/theme/app_theme.dart';

class ReadinessBanner extends StatelessWidget {
  final CompletionStatus status;
  final VoidCallback onVerifyEmail;
  final VoidCallback onSetupSignature;
  final VoidCallback onSetupSchedule;

  const ReadinessBanner({
    super.key,
    required this.status,
    required this.onVerifyEmail,
    required this.onSetupSignature,
    required this.onSetupSchedule,
  });

  @override
  Widget build(BuildContext context) {
    if (status.isComplete) return const SizedBox.shrink();

    final items = [
      _ReadinessItem(
        title: 'Verifikasi Email',
        subtitle: 'Konfirmasi OTP email perusahaan',
        done: status.emailVerified,
        icon: Icons.mark_email_read_outlined,
        onTap: onVerifyEmail,
      ),
      _ReadinessItem(
        title: 'Tanda Tangan Digital',
        subtitle: 'Untuk pengesahan dokumen',
        done: status.signatureSet,
        icon: Icons.draw_outlined,
        onTap: onSetupSignature,
      ),
      _ReadinessItem(
        title: 'Jadwal Kerja',
        subtitle: 'Normal atau Roster Shift',
        done: status.scheduleSet,
        icon: Icons.calendar_month_outlined,
        onTap: onSetupSchedule,
      ),
    ];

    final pct = (status.progress * 100).round();

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFFFFFBEB), Color(0xFFFEF3C7)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFFCD34D)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  color: const Color(0xFFD97706),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.verified_user_rounded, color: Colors.white, size: 20),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Kelengkapan Akun: ${status.completedCount}/3 Syarat Selesai ($pct%)',
                      style: const TextStyle(
                        fontWeight: FontWeight.w800,
                        fontSize: 13.5,
                        color: Color(0xFF78350F),
                      ),
                    ),
                    const SizedBox(height: 2),
                    const Text(
                      'Lengkapi syarat berikut untuk membuka seluruh formulir pengajuan.',
                      style: TextStyle(fontSize: 11.5, color: Color(0xFF92400E)),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          ClipRRect(
            borderRadius: BorderRadius.circular(99),
            child: LinearProgressIndicator(
              value: status.progress,
              minHeight: 7,
              backgroundColor: Colors.white.withValues(alpha: 0.7),
              valueColor: const AlwaysStoppedAnimation(Color(0xFFD97706)),
            ),
          ),
          const SizedBox(height: 12),
          Column(
            children: items.map((item) {
              return Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: InkWell(
                  onTap: item.done ? null : item.onTap,
                  borderRadius: BorderRadius.circular(14),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                        color: item.done
                            ? AppTheme.success.withValues(alpha: 0.35)
                            : const Color(0xFFFDE68A),
                      ),
                    ),
                    child: Row(
                      children: [
                        Container(
                          width: 34,
                          height: 34,
                          decoration: BoxDecoration(
                            color: (item.done ? AppTheme.success : const Color(0xFFD97706))
                                .withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Icon(
                            item.done ? Icons.check_circle_rounded : item.icon,
                            size: 18,
                            color: item.done ? AppTheme.success : const Color(0xFFD97706),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                item.title,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  fontWeight: FontWeight.w700,
                                  fontSize: 13,
                                  color: AppTheme.textPrimary,
                                ),
                              ),
                              Text(
                                item.subtitle,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  fontSize: 11,
                                  color: AppTheme.textSecondary,
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                          decoration: BoxDecoration(
                            color: (item.done ? AppTheme.success : AppTheme.primary)
                                .withValues(alpha: 0.10),
                            borderRadius: BorderRadius.circular(999),
                          ),
                          child: Text(
                            item.done ? 'Selesai' : 'Lengkapi',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w800,
                              color: item.done ? AppTheme.success : AppTheme.primary,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }
}

class _ReadinessItem {
  final String title;
  final String subtitle;
  final bool done;
  final IconData icon;
  final VoidCallback onTap;

  _ReadinessItem({
    required this.title,
    required this.subtitle,
    required this.done,
    required this.icon,
    required this.onTap,
  });
}
