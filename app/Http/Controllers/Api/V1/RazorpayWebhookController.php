<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\SeekerPackagePurchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayWebhookController extends Controller
{
    use ApiResponses;

    /**
     * Get Razorpay API Instance
     */
    protected function getRazorpayApi(): Api
    {
        return app(Api::class);
    }

    /**
     * Handle incoming Razorpay Webhook events
     */
    public function handle(Request $request): JsonResponse
    {
        $signature = $request->header('X-Razorpay-Signature');
        $webhookSecret = config('services.razorpay.webhook_secret');

        if (!$webhookSecret) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook secret is not configured.'
            ], 500);
        }

        if (!$signature) {
            return response()->json([
                'success' => false,
                'message' => 'Signature header missing.'
            ], 400);
        }

        try {
            $api = $this->getRazorpayApi();
            $payload = $request->getContent();

            // Verify webhook signature
            $api->utility->verifyWebhookSignature($payload, $signature, $webhookSecret);

            $eventData = json_decode($payload, true);
            $event = $eventData['event'] ?? '';

            if ($event === 'order.paid') {
                $orderId = $eventData['payload']['order']['entity']['id'] ?? '';
                $paymentId = $eventData['payload']['payment']['entity']['id'] ?? '';
                $paymentSignature = 'webhook_verified_' . time();

                $purchase = SeekerPackagePurchase::query()
                    ->where('razorpay_order_id', $orderId)
                    ->first();

                if ($purchase) {
                    $purchase->activate($paymentId, $paymentSignature);
                    return response()->json([
                        'success' => true,
                        'message' => 'Webhook processed and purchase activated.'
                    ], 200);
                }

                // Check if it's a Company Subscription Payment
                $companyPayment = \App\Models\CompanySubscriptionPayment::query()
                    ->where('razorpay_order_id', $orderId)
                    ->first();

                if ($companyPayment) {
                    if ($companyPayment->payment_status !== 'successful') {
                        $companyPayment->update([
                            'payment_status' => 'successful',
                            'razorpay_payment_id' => $paymentId,
                            'razorpay_signature' => $paymentSignature,
                            'purchased_at' => now(),
                        ]);

                        // Try to send success email
                        try {
                            $user = $companyPayment->company->user;
                            if ($user && $user->email && !\App\Support\Identifier::isSyntheticEmail($user->email)) {
                                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\CompanySubscriptionSuccessMail($companyPayment));
                            }
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::warning('[RazorpayWebhookController] Failed to send email: ' . $e->getMessage());
                        }
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Webhook processed and company subscription activated.'
                    ], 200);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Webhook event ignored.'
            ], 200);

        } catch (SignatureVerificationError $e) {
            return response()->json([
                'success' => false,
                'message' => 'Signature verification failed.'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook processing error: ' . $e->getMessage()
            ], 500);
        }
    }
}
