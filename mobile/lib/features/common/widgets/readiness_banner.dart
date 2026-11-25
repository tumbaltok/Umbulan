import 'package:material_ui/material_ui.dart';
import '../../../core/models/app_models.dart';
import '../../../core/theme/app_theme.dart';

class ReadinessBanner extends StatelessWidget {
  final CompletionStatus status;
  final VoidCallback onVerifyEmail;
  final VoidCallback onRegisterFace;
  final VoidCallback onSetupSignature;
  final VoidCallback onSetupSchedule;

  const ReadinessBanner({
    super.key,
    required this.status,
    required this.onVerifyEmail,
    required this.onRegisterFace,
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
        title: 'Biometrik Wajah',
        subtitle: 'Perekaman wajah 128-point',
        done: status.faceRegistered,
        icon: Icons.face_retouching_natural,
        onTap: onRegisterFace,
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
        color: const Color(0xFFFFFBEB),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFFDE68A)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.verified_user_outlined, color: Color(0xFFD97706)),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Kelengkapan Akun: ${status.completedCount}/4 Syarat Selesai ($pct%)',
                  style: const TextStyle(
                    fontWeight: FontWeight.w700,
                    fontSize: 14,
                    color: Color(0xFF92400E),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          ClipRRect(
            borderRadius: BorderRadius.circular(99),
            child: LinearProgressIndicator(
              value: status.progress,
              minHeight: 7,
              backgroundColor: const Color(0xFFFEF3C7),
              valueColor: const AlwaysStoppedAnimation(Color(0xFFD97706)),
            ),
          ),
          const SizedBox(height: 12),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: items.length,
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              crossAxisSpacing: 10,
              mainAxisSpacing: 10,
              childAspectRatio: 1.55,
            ),
            itemBuilder: (_, idx) {
              final item = items[idx];
              return InkWell(
                onTap: item.done ? null : item.onTap,
                borderRadius: BorderRadius.circular(12),
                child: Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: item.done
                          ? AppTheme.success.withValues(alpha: 0.4)
                          : const Color(0xFFFCD34D),
                    ),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          Icon(
                            item.done ? Icons.check_circle : item.icon,
                            size: 18,
                            color: item.done ? AppTheme.success : const Color(0xFFD97706),
                          ),
                          const Spacer(),
                          Text(
                            item.done ? 'Selesai' : 'Lengkapi',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w700,
                              color: item.done ? AppTheme.success : AppTheme.primary,
                            ),
                          ),
                        ],
                      ),
                      Text(
                        item.title,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontWeight: FontWeight.w700,
                          fontSize: 12.5,
                          color: AppTheme.textPrimary,
                        ),
                      ),
                      Text(
                        item.subtitle,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 10.5,
                          color: AppTheme.textSecondary,
                        ),
                      ),
                    ],
                  ),
                ),
              );
            },
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
