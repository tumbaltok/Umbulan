import 'package:material_ui/material_ui.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:umbulan/core/models/app_models.dart';
import 'package:umbulan/core/network/api_client.dart';
import 'package:umbulan/core/providers/auth_provider.dart';
import 'package:umbulan/core/services/push_notification_service.dart';
import 'package:umbulan/features/common/widgets/readiness_banner.dart';
import 'package:umbulan/main.dart';

void main() {
  test('UserModel parses 3-requirement completion status without phone field', () {
    final user = UserModel.fromJson({
      'id': 1,
      'name': 'Budi Operator',
      'email': 'budi@umbulan.co.id',
      'gender': 'Laki-laki',
      'level': 3,
      'schedule_type': 'normal',
      'roles': [
        {'role_name': 'Karyawan'},
      ],
      'has_signature': false,
      'is_approver': false,
      'is_account_complete': false,
      'completion_status': {
        'email_verified': true,
        'signature_set': false,
        'schedule_set': true,
        'is_complete': false,
      },
    });

    expect(user.email, 'budi@umbulan.co.id');
    expect(user.isPerempuan, false);
    expect(user.completionStatus.completedCount, 2);
    expect(user.completionStatus.progress, closeTo(2 / 3, 0.001));
    expect(user.isAccountComplete, false);
  });

  testWidgets('Login screen and 3-item ReadinessBanner render cleanly', (tester) async {
    final apiClient = ApiClient();
    final auth = AuthProvider(
      apiClient: apiClient,
      pushService: PushNotificationService(),
    );

    // Override bootstrapping state off via fake UserModel check on ReadinessBanner
    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: SingleChildScrollView(
            child: ReadinessBanner(
              status: CompletionStatus(
                emailVerified: true,
                signatureSet: false,
                scheduleSet: true,
                isComplete: false,
              ),
              onVerifyEmail: () {},
              onSetupSignature: () {},
              onSetupSchedule: () {},
            ),
          ),
        ),
      ),
    );

    expect(find.textContaining('2/3 Syarat Selesai (67%)'), findsOneWidget);
    expect(find.text('Verifikasi Email'), findsOneWidget);
    expect(find.text('Biometrik Wajah'), findsNothing);
    expect(find.text('Tanda Tangan Digital'), findsOneWidget);
    expect(find.text('Jadwal Kerja'), findsOneWidget);
    expect(find.textContaining('WhatsApp'), findsNothing);

    await tester.pumpWidget(UmbulanApp(overrideAuthProvider: auth));
    expect(find.textContaining('Memuat'), findsOneWidget);
  });
}
