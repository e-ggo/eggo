<?php
declare(strict_types=1);

namespace Eggo\lib;

use Generator;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * @mail eggo.com.cn@gmail.com
 * @author Eggo <https://www.eggo.com.cn>
 * @date 2022-02-25 3:06
 */
class GoMail
{
    /*
    $params = array(
    'Email' => '42321851@qq.com',
    'EmailUser' => 'UserName',
    'EmailCode' => 2226,
    'Title' => '(系统自动邮件,请勿回复1)',
    'Content' => sprintf('您的验证码是：%s如非本人操作无需理会', 'xxx')
    );
    $flag = GoMail::init()::send($args);
    */
    /**
     * @var array
     */
    private static $_instance = [];

    final private function __clone()
    {
    }

    /**
     * @var PHPMailer
     */
    protected static $mail;

    /**
     * @throws Exception
     */
    final private function __construct()
    {
        $Config = GoConfig::init()::get('mail');                // 获取配置文件内容

        self::$mail = new PHPMailer(true);
        self::$mail->CharSet = $Config['EmailChar'];                    // 设定邮件编码UTF8
        self::$mail->SMTPDebug = 0;                                     // Enable verbose debug output 开启DEBUG 1开启0关闭
        self::$mail->isSMTP();                                          // Set mailer to use 使用smtp鉴权方式发送邮件
        self::$mail->Host = $Config['EmailServer'];                     // Specify main and backup SM-TP servers【smtp.163.com|smtp.126.com|smtp.qq.com SMTP邮箱的服务器地址】
        self::$mail->SMTPAuth = true;                                   // Enable SM-TP authentication smtp需要鉴权 这个必须是true
        self::$mail->Username = $Config['EmailAddress'];                // SM-TP username smtp登录的账号 QQ邮箱即可
        self::$mail->Password = $Config['EmailPasswd'];;                // SM-TP password smtp登录的密码 使用邮箱后台生成的授权码
        self::$mail->SMTPSecure = $Config['EmailSMTPSecure'];;          // Enable TLS encryption,设置使用ssl加密方式登录鉴权
        self::$mail->Port = $Config['EmailPort'];;                      // TCP port to connect to 设置ssl连接smtp服务器的远程服务器端口号一般为 465
        self::$mail->FromName = 'eggo.com.cn';                          // 发件人昵称
        self::$mail->setFrom($Config['EmailAddress'], $Config['EmailNickname']);   //发件人邮箱地址与昵称
        self::$mail->addReplyTo($Config['EmailAddress'], '回复邮件');         //回复的时候回复给哪个邮箱 建议和发件人一致
    }

    /**
     * @throws Exception
     */
    final public static function init(...$args)
    {
        return static::$_instance[static::class] ?? static::$_instance[static::class] = new static(...$args);
    }


    /**
     *
     * @param ...$args
     * @return false|string
     * @author Eggo
     * @date 2022-02-25 3:08
     */
    public static function send(...$args)
    {
        $Result = self::sendEmail(...$args)->current();
        if ($Result) {
            return json_encode(array('status' => 'success', 'msg' => '邮件发送成功'), 448);
        } else return json_encode(array('status' => 'error', 'msg' => '邮件发送失败'), 448);
    }

    /**
     *
     * @param ...$args
     * @return Generator|void
     * @author Eggo
     * @date 2022-02-25 3:07
     */
    protected static function sendEmail(...$args)
    {
        $map = [];
        $Params = json_decode(json_encode($args, 320), true);
        array_map(function ($value) use (&$map) {
            $map = $value;
        }, $Params);

        if (is_array($map)) {
            $dateTime = date('Y-m-d H:i:s');
            if (empty($map['Email'])) return;
            $Email = $map['Email'];
            // 判断验证码传入
            if (empty($map['EmailCode'])) {
                $TXT = sprintf($dateTime . '尊敬的%s用户您接收的是系统自动邮件,无需回复!', $Email);
            } else $TXT = sprintf('尊敬的用户您的验证码是【%s】请在十分钟之内使用它', $map['EmailCode']);
            // $Email收件人邮箱地址$EmailUser收件人名称$TitleSubject邮件主题$Content邮件内容
            $EmailUser = empty($map['EmailUser']) ? ($map['EmailUser'] = $Email) : $map['EmailUser'];
            $Title = empty($map['Title']) ? (($map['Title'] = '(系统自动邮件,请勿回复)')) : $map['Title'];
            $Content = empty($map['Content']) ? (($map['Content'] = $TXT)) : $map['Content'];
            // Attachments 发送附件不能大10M AttName附件名称 AttPath附件路径 AltBody 纯文本信息
            $AttName = empty($map['AttName']) ? (($map['AttName'] = '请查收附件')) : $map['AttName'];
            $AltBody = empty($map['AltBody']) ? (($map['AltBody'] = '系统自动邮件')) : $map['AltBody'];

            try {
                // 添加发送收件人邮箱地址与名称
                self::$mail->addAddress($Email, $EmailUser);
                // 添加发送邮件附件 路径不存在不发送附件
                if (!empty($map['AttPath'])) self::$mail->addAttachment($map['AttPath'], $AttName);
                // Set email format to HTML 设置邮件正文内容是否为html编码
                self::$mail->isHTML(true);
                // 添加发送邮件的主题 为空值默认定义值
                self::$mail->Subject = $Title;
                // 添加发送邮件正文内容为空时 默认值
                self::$mail->Body = $Content;
                // 开始运行发送邮件
                yield self::$mail->send();
            } catch (Exception $e) {
                yield array('code' => -1, 'msg' => '邮件发送失败', self::$mail->ErrorInfo);
            }

        }

    }


}
