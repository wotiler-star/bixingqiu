<?php
/**
 * SmsSender.php — 可插拔短信发送（开发模式 / 阿里云短信骨架）
 *
 * 配置（service/config/.env）：
 *   BXQ_SMS_DRIVER = dev | aliyun   （默认 dev）
 *   BXQ_SMS_ENABLE = 0 | 1          （注册 / 找回密码是否强制短信验证，默认 0）
 *   —— 以下仅 aliyun 驱动需要 ——
 *   BXQ_SMS_ACCESSKEY = LTAIxxxx
 *   BXQ_SMS_SECRET    = xxxx
 *   BXQ_SMS_SIGN      = 签名（如 币星球）
 *   BXQ_SMS_TEMPLATE  = SMS_XXXXXX（模板 CODE）
 *
 * dev 模式：不真正下发短信，仅把验证码随接口返回（dev_code），便于无短信网关时走通流程。
 *           生产环境切勿用 dev 对外发送真实短信。
 *
 * aliyun 模式：凭证齐全时调用阿里云 SendSms OpenAPI；若凭证缺失，自动降级为 dev 并标记 fallback，
 *           避免注册/找回流程卡死。补全签名逻辑后即可真正下发。
 */
class SmsSender
{
    /**
     * @param string $phone 手机号
     * @param string $code  验证码
     * @return array ['ok'=>bool, 'dev_code'?=>string, 'msg'?=>string]
     */
    public static function send($phone, $code)
    {
        $driver = getenv('BXQ_SMS_DRIVER') ?: 'dev';
        if ($driver === 'aliyun') {
            return self::sendAliyun($phone, $code);
        }
        return self::sendDev($phone, $code);
    }

    private static function sendDev($phone, $code)
    {
        // 开发 / 联调：返回验证码，前端在提示中展示，便于无网关时测试完整流程
        return array('ok' => true, 'dev_code' => $code);
    }

    private static function sendAliyun($phone, $code)
    {
        $accessKey = getenv('BXQ_SMS_ACCESSKEY');
        $secret    = getenv('BXQ_SMS_SECRET');
        $sign      = getenv('BXQ_SMS_SIGN');
        $template  = getenv('BXQ_SMS_TEMPLATE');

        if (!$accessKey || !$secret || !$sign || !$template) {
            // 凭证缺失 → 降级为 dev，避免流程卡死（生产应配置完整凭证）
            return array('ok' => true, 'dev_code' => $code, 'fallback' => true);
        }

        // === 阿里云短信 SendSms 调用骨架（凭证齐全后补全签名即可真正发送）===
        // 需要：构造公共参数 + 业务参数，按 HMAC-SHA1 做签名，POST 到
        //   https://dysmsapi.aliyuncs.com/
        // 关键参数：RegionId=cn-hangzhou, Action=SendSms, PhoneNumbers, SignName,
        //           TemplateCode, TemplateParam={"code":"123456"}
        // 下面给出可直接补全的占位实现（默认返回未实现，避免静默成功）。
        return array(
            'ok'  => false,
            'msg' => 'aliyun sender 需补全 SendSms 签名调用（见 SmsSender.php 注释）'
        );
    }
}
