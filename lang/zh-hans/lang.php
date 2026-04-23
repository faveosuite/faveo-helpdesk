<?php

/*
  |--------------------------------------
  |   中文语言包
  |--------------------------------------
 */

return [
    /*
      |----------------------------------------------------------------------------------------
      | Authentication Pages [English(en)]
      |----------------------------------------------------------------------------------------
      |
      | The following language lines are used in all authentication related
      | issues to translate some words in view to English. You are free to
      | change them to anything you want to customize your views to better
      | match your application.
      |
     */

    /*
      |--------------------------------------
      |   Error
      |--------------------------------------
     */
    'success'                                                        => '成功',
    'fails'                                                          => '失败',
    'alert'                                                          => '提醒',
    'warning'                                                        => '警告',
    'required-error'                                                 => '请填写所有必填字段',
    'invalid'                                                        => '错误的用户名或密码',
    'sorry_something_went_wrong'                                     => '抱歉，出错了',
    'were_working_on_it_and_well_get_it_fixed_as_soon_as_we_can'     => '我们正在努力，我们会尽快把它修好.',
    'we_are_sorry_but_the_page_you_are_looking_for_can_not_be_found' => '很抱歉，你要的页面找不到了.',
    'go_back'                                                        => '返回',
    'the_board_is_offline'                                           => '面板已经下线',
    'error_establishing_connection_to_database'                      => '数据库连接错误',
    'unauthorized_access'                                            => '权限不足',
    'not-autherised'                                                 => '没有权限',
    'otp-not-matched'                                                => 'Oops! 你输入的OTP代码与我们发送给你的号码不一致.',
    'otp-invalid'                                                    => 'OTP代码必须是六位数字.',
    'otp-input-title'                                                => '输入六位数字.',
    'otp-expired'                                                    => '你的OTP代码已过期.<br/> 点击 "Resend OTP" 发送一个新的 OTP 代码到你的手机上.',
    'resend-otp-title'                                               => '点击这里重新发送 OTP',
    /*
      |--------------------------------------
      |   Login Page
      |--------------------------------------
     */
    'login_to_start_your_session'        => '登陆去启动你的会话',
    'login'                              => '登陆',
    'remember'                           => '登陆',
    'signmein'                           => '登陆标志',
    'iforgot'                            => '忘记密码',
    'email_address'                      => '邮箱地址',
    'password'                           => '密码',
    'password_confirmation'              => '确认密码',
    'woops'                              => 'Whoops!',
    'theirisproblem'                     => '检查文本框输入值是否正确.',
    'login'                              => '登陆',
    'e-mail'                             => '邮箱',
    'reg_new_member'                     => '注册一个会员',
    'this_account_is_currently_inactive' => '这个账号还为激活!',
    'not-registered'                     => '电子邮件/用户名未注册',
    'verify'                             => '验证',
    'enter-otp'                          => 'Enter OTP',
    'did-not-recive-code'                => '没有收到OTP?',
    'resend_otp'                         => '重新发送OTP',
    'verify-screen-msg'                  => '你的账户当前未激活.<br/>去激活你的账号, 请输入我们发送给你的OTP码',
    'otp-sent'                           => '我们已经像你发送了一个OTP码.',
    'verify-number'                      => '验证号码',
    'get-verify-message'                 => '输入我们发送给你的新的OTP码.',
    'number-verification-sussessfull'    => '您的号码已被成功验证，请稍候，我们正在更新您的资料.',
    'enter_your_email_here'              => '在这里输入你的电子邮件',
    /*
      |--------------------------------------
      |   Register Page
      |--------------------------------------
     */
    'registration'                                                                                => '注册',
    'full_name'                                                                                   => '全名',
    'firstname'                                                                                   => '名字',
    'lastname'                                                                                    => '姓氏',
    'profilepicture'                                                                              => '图像',
    'oldpassword'                                                                                 => '旧密码',
    'newpassword'                                                                                 => '新密码',
    'retype_password'                                                                             => '重新输入密码',
    'i_agree_to_the'                                                                              => '我同意',
    'terms'                                                                                       => '条款',
    'register'                                                                                    => '注册',
    'i_already_have_a_membership'                                                                 => '我已经有会员了',
    'see-profile1'                                                                                => '点这里查看 ',
    'see-profile2'                                                                                => '描述/概要',
    'activate_your_account_click_on_Link_that_send_to_your_mail'                                  => '激活你的账户!点击我们发送给您的邮件的链接',
    'activate_your_account_click_on_Link_that_send_to_your_mail_and_moble'                        => '激活您的帐户!点击我们发送到您的邮件或登录到您的帐户的链接，并输入我们发送给您的移动电话号码的OTP代码。',
    'activate_your_account_click_on_Link_that_send_to_your_mail_sms_plugin_inactive_or_not_setup' => '由于我们无法将OTP代码发送到您的手机和电子邮件到您的电子邮件地址，请与系统管理员联系.',
    'this_field_do_not_match_our_records'                                                         => '这个字段与我们的记录不符.',
    'we_have_e-mailed_your_password_reset_link'                                                   => '我们已经发送了你的密码重置链接!',
    "we_can't_find_a_user_with_that_e-mail_address"                                               => '我们找不到有那个电子邮件地址的用户.',
    /*
      |--------------------------------------
      |   Reset Password Page
      |--------------------------------------
     */
    'reset_password'              => '重置密码',
    'password-reset-successfully' => '您的密码已经重置。使用您的新密码登录到您的帐户',
    'password-can-not-reset'      => '我们无法重置您的密码，请稍后再试.',
    /*
      |--------------------------------------
      |   Forgot Password Page
      |--------------------------------------
     */
    'i_know_my_password'            => '我记得我的密码',
    'recover_passord'               => '恢复密码',
    'send_password_reset_link'      => '发送密码重置连接',
    'enter_email_to_reset_password' => '输入电子邮件/手机号码以重置密码',
    'link'                          => '连接',
    'email_or_mobile'               => '邮件或手机',
    /*
      |----------------------------------------------------------------------------------------
      | Emails Pages [English(en)]
      |----------------------------------------------------------------------------------------
      |
      | The following language lines are used in all Emails related issues to translate
      | some words in view to English. You are free to change them to anything you want to
      | customize your views to better match your application.
      |
     */
    'admin_panel' => '管理面板',
    /*
      |--------------------------------------
      |  Emails Create Page
      |--------------------------------------
     */
    'emails'                                                                           => '电子邮件',
    'incoming_emails'                                                                  => '收到邮件',
    'reuired_authentication'                                                           => '必须要认证',
    'fetching_email_via_imap'                                                          => '通过IMAP获取邮件',
    'create_email'                                                                     => '创建电子邮件',
    'email_address'                                                                    => '邮件地址',
    'email_name'                                                                       => '邮件名称',
    'help_topic'                                                                       => '帮助主题',
    'auto_response'                                                                    => '自动响应',
    'host_name'                                                                        => '主机名称',
    'port_number'                                                                      => '端口',
    'mail_box_protocol'                                                                => '邮箱协议',
    'authentication_required'                                                          => '必须认证',
    'yes'                                                                              => 'Yes',
    'no'                                                                               => 'No',
    'header_spoofing'                                                                  => '头欺骗',
    'allow_for_this_email'                                                             => '允许这封邮件',
    'imap_config'                                                                      => 'IMAP 配置',
    'email_information_and_settings'                                                   => '邮件信息及设置',
    'incoming_email_information'                                                       => '收到的邮件信息',
    'outgoing_email_information'                                                       => '发出的邮件信息',
    'new_ticket_settings'                                                              => '新工时设置',
    'protocol'                                                                         => '协议',
    'fetching_protocol'                                                                => '获取协议',
    'transfer_protocol'                                                                => '传输协议',
    'from_name'                                                                        => '发送人名字',
    'add_an_email'                                                                     => '添加邮件',
    'edit_an_email'                                                                    => '编辑邮件',
    'disable_for_this_email_address'                                                   => '禁用邮件地址',
    'validate_certificates_from_tls_or_ssl_server'                                     => '从TLS / SSL服务器验证证书',
    'authentication'                                                                   => '认证',
    'incoming_email_connection_failed_please_check_email_credentials_or_imap_settings' => '接受邮件连接失败!请检查电子邮件凭证或Imap设置',
    'outgoing_email_connection_failed'                                                 => '发送邮件连接失败!',
    'you_cannot_delete_system_default_email'                                           => '您不能删除系统默认电子邮件',
    'email_deleted_sucessfully'                                                        => '邮件删除成功',
    'email_can_not_delete'                                                             => '电子邮件可以不删除',
    'outgoing_email_failed'                                                            => '发送邮件失败',
    'system-email-not-configured'                                                      => '我们无法处理电子邮件请求，因为系统没有配置邮件发送邮件。请联系并报告系统管理员。.',
    /*
      |--------------------------------------
      |  Ban Emails Create Page
      |--------------------------------------
     */
    'ban_lists'                        => '禁用列表',
    'ban_email'                        => '禁用邮箱',
    'banlists'                         => '禁用列表',
    'ban_status'                       => '禁用状态',
    'list_of_banned_emails'            => '被禁用邮件列表',
    'edit_banned_email'                => '编辑禁用的电子邮件列表',
    'create_a_banned_email'            => '创建一个禁用的邮箱',
    'email_banned_sucessfully'         => '邮箱禁用成功',
    'email_can_not_ban'                => '邮箱不能被禁用',
    'banned_email_updated_sucessfully' => '禁用电子邮件更新成功',
    'banned_email_not_updated'         => '禁用的电子邮件没有被更新',
    'banned_removed_sucessfully'       => '禁用电子邮件删除成功',
    /*
      |--------------------------------------
      |  Templates Index Page
      |--------------------------------------
     */
    'templates'                                => '模版',
    'template_set'                             => '模版设置',
    'create_template'                          => '创建模版',
    'edit_template'                            => '编辑模版',
    'list_of_templates_sets'                   => '模版设置列表',
    'create_set'                               => '创建组',
    'template_name'                            => '模版名称',
    'template_saved_successfully'              => '模版保存成功',
    'template_updated_successfully'            => '模版更新成功',
    'in_use'                                   => '使用中',
    'you_have_created_a_new_template_set'      => '你创建了一个模版设置',
    'you_have_successfully_activated_this_set' => '你成功激活了这个模版配置',
    'template_set_deleted_successfully'        => '模版配置成功删除',
    //Template Description
    'Create ticket agent'       => '创建工时时发送给代理和管理的通知电子邮件',
    'Assign ticket'             => '分配给代理人的工时',
    'Create ticket'             => '邮件发送到客户端获得成功的消息',
    'Check ticket'              => '如果客户想通过客户端检查工时，那么链接将被发送到客户端。此链接用于客户端查看工时的详细信息，而无需登录系统',
    'Ticket reply agent'        => '一旦客户对工时作出答复，通知就会发送给代理电子邮件',
    'Registration notification' => '密码和用户名是在第一次注册时通过电子邮件发送的',
    'Reset password'            => '电子邮件与密码重置链接',
    'Error report'              => '错误报告',
    'Ticket creation'           => '系统发送的第一个通知给客户端',
    'Ticket reply'              => '代理商在工时上的回复，电子邮件通知发送给客户和合作者',
    'Close ticket'              => '寄给客户的邮件在工时关闭时',
    'Create ticket by agent'    => '代理以客户机的名称为客户机创建一个工时',
    /*
      |--------------------------------------
      |  Templates Create Page
      |--------------------------------------
     */
    'template_set_to_clone' => '克隆模板设置',
    'language'              => '语言',
    /*
      |--------------------------------------
      |  Diagnostics Page
      |--------------------------------------
     */
    'diagnostics'                => '诊断',
    'from'                       => '来自',
    'to'                         => '到',
    'subject'                    => '主题',
    'message'                    => '信息',
    'send'                       => '发送',
    'choose_an_email'            => '选择一封邮件',
    'email_diagnostic'           => '邮件诊断',
    'send-mail-to-diagnos'       => '发送邮件检查发送的电子邮件设置',
    'message_has_been_sent'      => '消息已经发送',
    'message_sent_from_php_mail' => '从PHP-Mail发送消息',
    'mailer_error'               => '邮件错误',
    /*
      |----------------------------------------------------------------------------------------
      | Settings Pages [English(en)]
      |----------------------------------------------------------------------------------------
      |
      | The following language lines are used in all Setting related issues to translate
      | some words in view to English. You are free to change them to anything you want to
      | customize your views to better match your application.
      |
     */

    /*
      |--------------------------------------
      |   Company Settings Page
      |--------------------------------------
     */
    'country-code'                 => '国家代码',
    'company'                      => '公司',
    'company_settings'             => '公司设置',
    'website'                      => '网站',
    'phone'                        => '电话',
    'address'                      => '地址',
    'landing'                      => '登陆页面',
    'offline'                      => '退出页面',
    'thank'                        => '感谢页面',
    'logo'                         => 'Logo',
    'save'                         => '保存',
    'delete-logo'                  => '删除 logo',
    'click-delete'                 => '点击这里删除',
    'use_logo'                     => '使用 logo',
    'company_updated_successfully' => '公司更新成功',
    'company_can_not_updated'      => '公司无法更新',
    'enter-country-phone-code'     => '输入你城市的手机号码',
    'country-code-required-error'  => '必须使用手机号码.',
    'incorrect-country-code-error' => '错误的城市号码.',
    /*
      |--------------------------------------
      |   System Settings Page
      |--------------------------------------
     */
    'system'                                                     => '系统',
    'online'                                                     => '线上',
    'offline'                                                    => '线下',
    'name/title'                                                 => '名称/标题',
    'pagesize'                                                   => '页面尺寸',
    'url'                                                        => 'URL',
    'default_department'                                         => '默认部门',
    'loglevel'                                                   => '日志登记',
    'purglog'                                                    => 'Purge logs',
    'nameformat'                                                 => '名字格式',
    'timeformat'                                                 => '时间格式',
    'date'                                                       => '日期',
    'dateformat'                                                 => '日期格式',
    'date_time'                                                  => '日期与时间格式',
    'day_date_time'                                              => '天, 日期和时间格式',
    'timezone'                                                   => '默认失去',
    'Ticket-created-successfully'                                => '工时创建成功!',
    'Ticket-created-successfully2'                               => '工时已经创建，但没有得到验证。一旦用户验证了他们的帐户，它就会显示在收件箱中.',
    'system_updated_successfully'                                => '系统更新成功',
    'system_can_not_updated'                                     => '系统无法更新',
    'ticket_updated_successfully'                                => '工时更新成功',
    'ticket_can_not_updated'                                     => '工时无法更新',
    'email_updated_successfully'                                 => '邮箱更新成功',
    'email_can_not_updated'                                      => '邮箱无法更新',
    'select_a_time_zone'                                         => '选择一个时区',
    'select_a_date_time_format'                                  => '选择一个日期格式',
    'Ticket-has-been-created-successfully-your-ticket-number-is' => '工时已经创建成功，你的号码是',
    'Please-save-this-for-future-reference'                      => '请保存这个以备将来参考',
    'email-moble-already-taken'                                  => '电子邮件或手机号码已经被使用',
    'mobile-has-been-taken'                                      => '电话号码已经使用',
    'failed-to-create-user-tcket-as-mobile-has-been-taken'       => '未能创建新工时，因为您输入的移动电话号码是与用户连接的。但是，您输入的其他细节并不匹配用户的详细信息。请验证用户详细信息或创建新用户',
    'rtl'                                                        => 'RTL (从右到左)',
    'the_rtl_support_is_only_applicable_to_the_outgoing_mails'   => 'RTL仅支持发出的邮件',
    'user_set_ticket_status'                                     => '允许用户去设置工时状态',
    'send_otp_for_account_verfication'                           => '发送 OTP 到用户',
    'otp_usage_info'                                             => '如果您不允许未经验证的用户创建工时，我们将发送带有验证链接的电子邮件和带有OTP代码的短信给用户。如果电子邮件被设置为非强制用户，将会收到用户的用户名和密码。[注:SMS将使用Faveo SMS插件发送].',
    'send_otp_title_message'                                     => '发送OTP进行用户帐号验证，重置密码和手机号码验证',
    'allow_unverified_users_to_create_ticket'                    => '允许未经验证的用户创建工时',
    'make-email-mandatroy'                                       => '为用户创建电子邮件',
    'email_man_info'                                             => '如果你的邮件不是强制性的，用户可以在没有电子邮件的情况下注册。我们建议您禁止未经验证的用户创建票证，这样用户就可以在他们的手机号码上接收通知，并使用用户名和密码登录他们的账户.',
    /*
      |--------------------------------------
      |   Email Settings Page
      |--------------------------------------
     */
    'email'                               => '邮件',
    'email-settings'                      => '邮件设置',
    'default_template'                    => '默认模版设置:',
    'default_system_email'                => '默认系统邮件:',
    'default_alert_email'                 => '默认提醒邮件:',
    'admin_email'                         => '管理员的电子邮件地址:',
    'email_fetch'                         => '电子邮件获取:',
    'enable'                              => '启用',
    'default_MTA'                         => '默认MTA',
    'fetch_auto-corn'                     => '获取自动任务',
    'strip_quoted_reply'                  => '带引用回复',
    'reply_separator'                     => '回复分隔符标记',
    'accept_all_email'                    => '接受所有邮件',
    'accept_email_unknown'                => '从未知用户获取邮件',
    'accept_email_collab'                 => '接受邮件的合作者',
    'automatically_and_collab_from_email' => '自动添加来自电子邮件领域的合作者',
    'default_alert_email'                 => '默认提醒邮件',
    'attachments'                         => '附件',
    'email_attahment_user'                => '给用户的电子邮件附件',
    'cron_notification'                   => '启用通知任务',
    'cron'                                => '工程进度',
    'cron-jobs'                           => '定时任务',
    'crone-url-message'                   => '这些是Faveo的作业调度器(cron作业)的URL.',
    'clipboard-copy-message'              => '复制到剪贴板.',
    'click'                               => '点击这里',
    'check-cron-set'                      => '检查如何在服务器上设置cron作业.',
    'notification-email'                  => '每日总结',
    'click-url-copy'                      => '点击这里复制URL',
    'job-scheduler-error'                 => '无法更新作业调度器.',
    'job-scheduler-success'               => '作业调度器成功更新.',
    /*
      |--------------------------------------
      |   Ticket Settings Page
      |--------------------------------------
     */
    'ticket'                             => '票券',
    'ticket-setting'                     => '票券设置',
    'default_ticket_number_format'       => '默认票券数量形式',
    'default_ticket_number_sequence'     => '默认票券号码序列',
    'default_status'                     => '默认状态',
    'default_priority'                   => '默认优先级',
    'default_sla'                        => '默认SLA',
    'default_help_topic'                 => '默认帮助主题',
    'maximum_open_tickets'               => '最大开放票据',
    'agent_collision_avoidance_duration' => '代理冲突避免持续时间',
    'human_verification'                 => '人类验证',
    'claim_on_response'                  => '要求响应',
    'assigned_tickets'                   => '分配票券',
    'answered_tickets'                   => '答复票券',
    'agent_identity_masking'             => '代理身份屏蔽',
    'enable_HTML_ticket_thread'          => '启用HTML票券线',
    'allow_client_updates'               => '允许客户端更新',
    'lock_ticket_frequency'              => '锁票频率',
    'only-once'                          => '仅有一次',
    'frequently'                         => '经常地',
    'reload-now'                         => '即可重载',
    'ticket-lock-inactive'               => '你已经怠惰了一段时间。请重新加载页面.',
    'make-system-default-mail'           => '使用这个电子邮件系统的默认电子邮件',
    'thread'                             => '路线',
    'labels'                             => '标签',
    /*
      |--------------------------------------
      |   Access Settings Page
      |--------------------------------------
     */
    'access'                                           => '权限',
    'expiration_policy'                                => '密码过期策略',
    'allow_password_resets'                            => '允许密码重置',
    'reset_token_expiration'                           => '重置token有效期',
    'agent_session_timeout'                            => '代理会话超时',
    'bind_agent_session_IP'                            => '绑定代理会话到IP',
    'registration_required'                            => '必须注册',
    'require_registration_and_login_to_create_tickets' => '需要注册和登录才能创建门票',
    'registration_method'                              => '注册方法',
    'user_session_timeout'                             => '用户会话超时',
    'client_quick_access'                              => '客户端快速访问',
    'cron'                                             => '任务',
    'cron_settings'                                    => '任务设置',
    'system-settings'                                  => '系统设置',
    'settings-2'                                       => '设置',
    /*
      |--------------------------------------
      |   Auto-Response Settings Page
      |--------------------------------------
     */
    'auto_responce'                      => '自动响应',
    'auto_responce-settings'             => '自动相应设置',
    'new_ticket'                         => '新的票据',
    'new_ticket_by_agent'                => '新的票据代理',
    'new_message'                        => '新消息',
    'submitter'                          => '提交人 : ',
    'send_receipt_confirmation'          => '发送收据确认',
    'participants'                       => '参与者 : ',
    'send_new_activity_notice'           => '发送新活动通知',
    'overlimit_notice'                   => '超过通知上线',
    'email_attachments_to_the_user'      => '用户邮件附件',
    'auto_response_updated_successfully' => '自动响应更新成功',
    'auto_response_can_not_updated'      => '自动响应无法更新',
    /*
      |--------------------------------------
      |   Alert & Notice Settings Page
      |--------------------------------------
     */
    'disable'                                               => '禁用',
    'admin_email_2'                                         => '管理员邮箱',
    'alert_notices'                                         => '提醒和通知',
    'alert_notices_setitngs'                                => '提醒和通知设置',
    'new_ticket_alert'                                      => '新票据提醒',
    'department_manager'                                    => '部门经理',
    'department_members'                                    => '部门成员',
    'organization_account_manager'                          => '机构账户经理',
    'new_message_alert'                                     => '新消息提醒',
    'last_respondent'                                       => '最后应答的',
    'assigned_agent_team'                                   => '指定代理/团队',
    'new_internal_note_alert'                               => '新的内部通知提醒',
    'ticket_assignment_alert'                               => '票据分配提醒',
    'team_lead'                                             => '团队领导',
    'team_members'                                          => '团队成功',
    'ticket_transfer_alert'                                 => '票据转移提醒',
    'overdue_ticket_alert'                                  => '过期票据提醒',
    'system_alerts'                                         => '系统提醒',
    'system_errors'                                         => '系统错误',
    'SQL_errors'                                            => 'SQL 错误',
    'excessive_failed_login_attempts'                       => '登陆失败次数过多',
    'system_error_reports'                                  => '系统错误报告',
    'Send_app_crash_reports_to_help_Ladybird_improve_Faveo' => '发送应用程序崩溃报告帮助ladybird改进Faveo',
    'alert_&_notices_updated_successfully'                  => '警告和通知更新成功',
    'alert_&_notices_can_not_updated'                       => '警告和通知不能更新',
    /*
      |-----------------------------------------------
      | Ratings Settings
      |-----------------------------------------------
     */
    'current_ratings' => '当前等级',
    'edit_ratings'    => '编辑等级',
    /*
      |-------------------------------------------------
      |Social login
      |--------------------------------------------------
     */
    'social-login' => '社会化登陆',
    /*
      |------------------------------------------------
      | Language page
      |------------------------------------------------
     */
    'default'            => '默认',
    'language-settings'  => '语言设置',
    'iso-code'           => 'ISO-代码',
    'download'           => '下载',
    'upload_file'        => '上传文件',
    'enter_iso-code'     => '输入ISO代码',
    'eg.'                => '例子',
    'for'                => 'for',
    'english'            => '英语',
    'language-name'      => '语言名字',
    'file'               => '文件',
    'read-more'          => '阅读更多.',
    'enable_lang'        => '启用.',
    'add-lang-package'   => '增加新语言包',
    'package_exist'      => '语言包已经存在.',
    'iso-code-error'     => '错误的ISO代码。输入正确的ISO代码.',
    'zipp-error'         => 'zip文件中有错误。Zip必须包含PHP文件.',
    'upload-success'     => '上传成功.',
    'file-error'         => '错误或无效的文件.',
    'delete-success'     => '语言包删除成功.',
    'lang-doesnot-exist' => '语言包不存在.',
    'active-lang-error'  => '语言包在激活状态下无法删除.',
    'language-error'     => '语言包在目录中未发现.',
    'lang-fallback-lang' => '不能删除系统备用的语言包',
    /*
      |--------------------------------------
      | Plugin Settings
      |--------------------------------------
     */
    'add_plugin'            => '增加插件',
    'plugins'               => '插件',
    'upload'                => '上传',
    'plugins-list'          => '插件列表',
    'plugin-exists'         => '插件已经存在',
    'plugin-installed'      => '插件安装成功.',
    'plugin-path-missing'   => '插件文件路径不存在',
    'no-plugin-file'        => '没有 ',
    'plugin-config-missing' => '没有 <b>config.php or ServiceProvider.php</b>',
    'plugin-info'           => '你是程序员吗?我们鼓励您编写自己的插件，并为社区提供这些插件.',
    'plugin-info-pro'       => '检查pro版本可用的插件.',
    'click-here'            => '点击这里',
    /*
      |----------------------------------------------------------------------------------------
      | Manage Pages [English(en)]
      |----------------------------------------------------------------------------------------
      |
      | The following language lines are used in all Manage related issues to translate
      | some words in view to English. You are free to change them to anything you want to
      | customize your views to better match your application.
      |
     */
    'manage' => '管理',
    /*
      |--------------------------------------
      |  Help Topic index Page
      |--------------------------------------
     */
    'help_topics'       => '帮助主题',
    'topic'             => '主题',
    'type'              => '类型',
    'priority'          => '优先',
    'last_updated'      => '最后更新',
    'create_help_topic' => '创建帮助主题',
    'action'            => '操作',
    /*
      |--------------------------------------
      |  Help Topic Create Page
      |--------------------------------------
     */
    'active'                                => '激活',
    'disabled'                              => '禁用',
    'public'                                => '公共的',
    'private'                               => '私有的',
    'parent_topic'                          => '父级主题',
    'Custom_form'                           => '自定义表单',
    'SLA_plan'                              => 'SLA 计划',
    'sla-plans'                             => 'SLA 计划',
    'auto_assign'                           => '自动分配',
    'auto_respons'                          => '自动响应',
    'ticket_number_format'                  => '工单编号格式化',
    'system_default'                        => '系统默认',
    'custom'                                => '自定义',
    'internal_notes'                        => '内部笔记',
    'select_a_parent_topic'                 => '选择一个父级主题',
    'custom_form'                           => '自定义表单',
    'select_a_form'                         => '选择一个表单',
    'select_a_department'                   => '选择一个部门',
    'departments'                           => '部门',
    'select_a_priority'                     => '选择优先级',
    'priorities'                            => '选择优先级',
    'select_a_sla_plan'                     => '选择一个SLA计划',
    'sla_plans'                             => 'SLA 计划',
    'select_an_agent'                       => '选择一个代理',
    'helptopic_created_successfully'        => '帮助主题创建成功',
    'helptopic_can_not_create'              => '无法创建帮助主题',
    'helptopic_updated_successfully'        => '帮助主题更新成功',
    'helptopic_can_not_update'              => '无法更新帮助主题',
    'you_cannot_delete_default_department'  => '你不能删除默认部门',
    'have_been_moved_to_default_help_topic' => '是否移动到默认主题',
    'helptopic_deleted_successfully'        => '成功删除帮助主题',
    'make-default-helptopic'                => '设置默认帮助主题',
    /*
      |--------------------------------------
      |  SLA plan Index Page
      |--------------------------------------
     */
    'sla_plans'    => 'SLA 计划',
    'create_SLA'   => '创建一个 SLA',
    'grace_period' => '宽限期',
    'added_date'   => '添加日期',
    /*
      |--------------------------------------
      |  SLA plan Create Page
      |--------------------------------------
     */
    'transient'                                            => '短暂',
    'ticket_overdue_alert'                                 => '工单过时提醒',
    'sla_plan_created_successfully'                        => 'SLA 计划创建成功',
    'sla_plan_can_not_create'                              => 'SLA plan 无法创建',
    'sla_plan_updated_successfully'                        => 'SLA plan 更新成功',
    'sla_plan_can_not_update'                              => 'SLA plan 无法更新',
    'you_cannot_delete_default_department'                 => '你不能删除默认部分',
    'have_been_moved_to_default_sla'                       => '移动到默认的 SLA',
    'associated_department_have_been_moved_to_default_sla' => '关联的部门已被转移到默认SLA',
    'associated_help_topic_have_been_moved_to_default_sla' => '关联的部门已被转移到默认SLA',
    'sla_plan_deleted_successfully'                        => 'SLA 计划删除成功',
    'sla_plan_can_not_delete'                              => 'SLA 计划无法删除',
    'make-default-sla'                                     => '设置默认SLA计划',
    /*
      |--------------------------------------
      |  Work Flow
      |--------------------------------------
     */
    'workflow'                      => '工作流程',
    'ticket_workflow'               => '工单工作流程',
    'create_workflow'               => '创建工作流',
    'edit_workflow'                 => '编辑',
    'updated'                       => '更新',
    'target'                        => '目标',
    'target_channel'                => '目标频道',
    'execution_order'               => '执行顺序',
    'target_channel'                => '目标频道',
    'workflow_rules'                => '工作流规则',
    'workflow_action'               => '工作流操作',
    'rules'                         => '规则',
    'order'                         => '书怒',
    'condition'                     => '条件',
    'statement'                     => '声明',
    'select_a_channel'              => '选择一个频道',
    'body'                          => '主题',
    'select_one'                    => '选择一个',
    'equal_to'                      => '等于',
    'not_equal_to'                  => '不等于',
    'contains'                      => '包含',
    'does_not_contain'              => '不包含',
    'starts_with'                   => '开始于',
    'ends_with'                     => '结束于',
    'select_an_action'              => '选择一个操作',
    'reject_ticket'                 => '驳回工单',
    'set_department'                => '设置部门',
    'set_priority'                  => '设置优先级',
    'set_sla_plan'                  => '设置SLA计划',
    'assign_team'                   => '分配团队',
    'assign_agent'                  => '分配代理',
    'set_help_topic'                => '设置帮助主题',
    'set_ticket_status'             => '设置工单状态',
    'workflow_created_successfully' => '工作流创建成功',
    'workflow_updated_successfully' => '工作流更新成功',
    'workflow_deleted_successfully' => '工作流删除成功',
    /*
      |--------------------------------------
      |  Form Create Page
      |--------------------------------------
     */
    'title'                                 => '标题',
    'instruction'                           => '说明',
    'label'                                 => '标签',
    'visibility'                            => '可见的',
    'variable'                              => '变量',
    'create_form'                           => '创建表单',
    'forms'                                 => '表单',
    'form_name'                             => '表单名称',
    'view_this_form'                        => '查看表单',
    'delete_from'                           => '删除表单',
    'are_you_sure_you_want_to_delete'       => '你确信你想要删除表单吗',
    'close'                                 => '关闭',
    'instructions'                          => '说明',
    'instructions_on_creating_form'         => '选择要添加到下面表单的字段类型，然后单击“类型”下拉。如果类型是select、checkbox或radio，不要忘记设置字段选项。用逗号分隔每个选项。完成创建表单后，可以单击“save form”按钮保存表单',
    'form_properties'                       => '表单属性',
    'adding_fields'                         => '增加字段中',
    'click_add_fields_button_to_add_fields' => "点击 <b>'增加字段'</b> 按钮去增加字段",
    'add_fields'                            => '增加字段',
    'save_form'                             => '保存表单',
    'label'                                 => '标签',
    'name'                                  => '名称',
    'type'                                  => '类型',
    'values(selected_fields)'               => '值 (选择的字段)',
    'required'                              => '必须',
    'Action'                                => '操作',
    'remove'                                => '移除',
    'form_deleted_successfully'             => '成功删除表单',
    'successfully_created_form'             => '成功创建表单',
    'please_fill_form_name'                 => '请填写表单名字',
    'category_inserted_successfully'        => '分类插入成功',
    'category_not_inserted'                 => '不能插入分类',
    'category_updated_successfully'         => '分类更新成功',
    'category_not_updated'                  => '不能更新分类',
    'category_deleted_successfully'         => '分类删除成功',
    'category_not_deleted'                  => '分类不能删除',
    'article_inserted_successfully'         => '',
    'article_not_inserted'                  => '文章插入成功',
    'article_updated_successfully'          => '不能插入文章',
    'article_not_updated'                   => '文章更新成功',
    'article_deleted_successfully'          => '不能更新文章',
    'article_not_deleted'                   => '文章删除成功',
    'article_can_not_deleted'               => '文章不能被删除',
    'page_created_successfully'             => '页面创建成功',
    'your_page_updated_successfully'        => '页面更新成功',
    'page_deleted_successfully'             => '页面删除成功',
    'settings_updated_successfully'         => '设置更新成功',
    'settings_can_not_updated'              => '设置不能更新',
    'can_not_process'                       => 'Can not process',
    'comment_published'                     => '发布评论',
    'comment_deleted'                       => '删除评论',
    'publish_time'                          => '发布时间',
    /*
      |----------------------------------------------------------------------------------------
      | Theme Pages [English(en)]
      |----------------------------------------------------------------------------------------
      |
      | The following language lines are used in all Theme related issues to translate
      | some words in view to English. You are free to change them to anything you want to
      | customize your views to better match your application.
      |
     */
    'themes' => '主题',
    /*
      |--------------------------------------
      |  Footer Pages
      |--------------------------------------
     */
    'footer'  => '页脚',
    'footer1' => '页脚1',
    'footer2' => '页脚2',
    'footer3' => '页脚3',
    'footer4' => '页脚4',
    /*
      |--------------------------------------
      |  Custom alert box
      |--------------------------------------
     */
    'ok'             => 'Ok',
    'cancel'         => '取消',
    'select-ticket'  => '请选择工单.',
    'confirm'        => '你确定吗?',
    'delete-tickets' => '删除工单',
    'close-tickets'  => '关闭工单',
    'open-tickets'   => '打开工单',
    /*
      |----------------------------------------------------------------------------------------
      | Staff Pages [English(en)]
      |----------------------------------------------------------------------------------------
      |
      | The following language lines are used in all Staff related issues to translate
      | some words in view to English. You are free to change them to anything you want to
      | customize your views to better match your application.
      |
     */
    'are_you_sure'       => '你确定',
    'staffs'             => '职员',
    'name'               => '名称',
    'user_name'          => '用户名',
    'status'             => '状态',
    'group'              => '分组',
    'department'         => '部门',
    'created'            => '创建日期',
    'lastlogin'          => '最近登陆',
    'createagent'        => '创建代理',
    'delete'             => '删除',
    'agents'             => '代理',
    'create'             => '创建',
    'edit'               => '编辑',
    'departments'        => '部门',
    'groups'             => '分组',
    'select_a_time_zone' => '选择时区',
    'time_zones'         => '时区',
    /*
      |--------------------------------------
      |  Staff Create Page
      |--------------------------------------
     */
    'create_agent'                            => '创建代理',
    'first_name'                              => '名字',
    'last_name'                               => '姓氏',
    'mobile_number'                           => '手机号码',
    'agent_signature'                         => '代理签名',
    'account_status_setting'                  => '账户状态设置',
    'account_type'                            => '账户类型',
    'admin'                                   => '管理员',
    'agent'                                   => '代理',
    'account_status'                          => '账户状态',
    'locked'                                  => '锁定',
    'assigned_group'                          => '分配组',
    'primary_department'                      => '主要部门',
    'agent_time_zone'                         => '代理时区',
    'day_light_saving'                        => '省电',
    'limit_access'                            => '限制访问',
    'directory_listing'                       => '目录列表',
    'vocation_mode'                           => '职业模式',
    'assigned_team'                           => '分配团队',
    'agent_send_mail_error_on_agent_creation' => '发送邮件到代理时出错. 请检查邮件配置并重试',
    'agent_creation_success'                  => '代理创建成功',
    'failed_to_create_agent'                  => '代理创建失败',
    'failed_to_edit_agent'                    => '代理编辑失败',
    'agent_updated_sucessfully'               => '代理更新成功',
    'unable_to_update_agent'                  => '无法更新代理',
    'agent_deleted_sucessfully'               => '代理删除成功',
    'this_staff_is_related_to_some_tickets'   => '这些职员与一些工单有关',
    'list_of_agents'                          => '列出代理',
    'create_an_agent'                         => '创建代理',
    'edit_an_agent'                           => '编辑代理',
    /*
      |--------------------------------------
      |  Department Create Page
      |--------------------------------------
     */
    'create_department'                                => '创建部门',
    'manager'                                          => '管理者',
    'ticket_assignment'                                => '工单分配',
    'restrict_ticket_assignment_to_department_members' => '限制部门员工工单转让',
    'outgoing_emails'                                  => '外发邮件',
    'outgoing_email'                                   => '外发邮件',
    'template_set'                                     => '模版设置',
    'auto_responding_settings'                         => '自动响应设置',
    'disable_for_this_department'                      => '禁用这些部门',
    'auto_response_email'                              => '自动响应邮件',
    'recipient'                                        => '接受人',
    'group_access'                                     => '访问组',
    'department_signature'                             => '部门签名',
    'list_of_departments'                              => '部门列表',
    'create_a_department'                              => '创建部门',
    'outgoing_email_settings'                          => '外发邮件设置',
    'edit_department'                                  => '编辑部门',
    'select_a_sla'                                     => '选择 SLA',
    'select_a_manager'                                 => '选择一个管理',
    'department_created_sucessfully'                   => '部门创建成功',
    'failed_to_create_department'                      => '部门创建失败',
    'department_updated_sucessfully'                   => '部门更新成功',
    'department_not_updated'                           => '部门无法更新',
    'you_cannot_delete_default_department'             => '你不能删除默认部门',
    'have_been_moved_to_default_department'            => '是否移动到默认部门',
    'the_associated_helptopic_has_been_deactivated'    => '相关的帮助主题已被停用',
    'department_deleted_sucessfully'                   => '部门删除成功',
    'department_can_not_delete'                        => '部门无法删除',
    'select_a_department'                              => '选择部门',
    'make-default-department'                          => '设置默认部门',
    /*
      |--------------------------------------
      |  Team Create Page
      |--------------------------------------
     */
    'create_team'                => '创建团队',
    'team_lead'                  => '团队领导',
    'assignment_alert'           => '任务提醒',
    'disable_for_this_team'      => '禁用这个团队',
    'teams'                      => '团队',
    'list_of_teams'              => '团队列表',
    'create_a_team'              => '创建团队',
    'edit_a_team'                => '编辑团队',
    'teams_created_successfully' => '团队创建成功',
    'teams_can_not_create'       => '团队不能创建',
    'teams_updated_successfully' => '团队更新成功',
    'teams_can_not_update'       => '团队无法更新',
    'teams_deleted_successfully' => '团队删除成功',
    'teams_can_not_delete'       => '团队不能删除',
    'select_a_team'              => '选择团队',
    'select_a_team_lead'         => '选择团队领导',
    'members'                    => '成员',
    /*
      |--------------------------------------
      |  Group Create Page
      |--------------------------------------
     */
    'create_group'                                                                           => '创建分组',
    'goups'                                                                                  => '分组',
    'can_create_ticket'                                                                      => '可以创建工单',
    'can_edit_ticket'                                                                        => '可以编辑工单',
    'can_post_ticket'                                                                        => '可以提交工单',
    'can_close_ticket'                                                                       => '可以关闭工单 ',
    'can_assign_ticket'                                                                      => '可以分配工单',
    'can_transfer_ticket'                                                                    => '可以传送工单',
    'can_delete_ticket'                                                                      => '可以删除工单',
    'can_ban_emails'                                                                         => '可以禁止邮件',
    'can_manage_premade'                                                                     => '可以管理半成品',
    'can_manage_FAQ'                                                                         => '可以管理常见问题',
    'can_view_agent_stats'                                                                   => '看一查看代理状态',
    'department_access'                                                                      => '部门权限',
    'admin_notes'                                                                            => '管理记录',
    'group_members'                                                                          => '组成员',
    'group_name'                                                                             => '组名称',
    'select_a_group'                                                                         => '选择组',
    'create_a_group'                                                                         => '创建组',
    'edit_a_group'                                                                           => '编辑组',
    'group_created_successfully'                                                             => '组创建成功',
    'group_can_not_create'                                                                   => '组无法创建',
    'group_updated_successfully'                                                             => '组更新成功',
    'group_can_not_update'                                                                   => '组无法更新',
    'there_are_agents_assigned_to_this_group_please_unassign_them_from_this_group_to_delete' => '有分配到这个组的代理. 请先删除他们从这个组',
    'group_cannot_delete'                                                                    => '组不能被删除',
    'group_deleted_successfully'                                                             => '组删除成功',
    'group_cannot_delete'                                                                    => '组不能被删除',
    'failed_to_load_the_page'                                                                => '加载页面失败',
    /*
      |--------------------------------------
      |  SMTP Page
      |--------------------------------------
     */
    'driver'     => '驱动',
    'smtp'       => 'SMTP',
    'host'       => '主机',
    'port'       => '端口',
    'encryption' => '加密',
    /*
      |----------------------------------------------------------------------------------------
      | Agent Panel [English(en)]
      |----------------------------------------------------------------------------------------
      |
      | The following language lines are used in all Agent Panel related issues to translate
      | some words in view to English. You are free to change them to anything you want to
      | customize your views to better match your application.
      |
     */
    'agent_panel'        => '代理面板',
    'profile'            => '简要描述',
    'change_password'    => '改变密码',
    'sign_out'           => '退出',
    'Tickets'            => '工单',
    'ticket-details'     => '工单详情',
    'inbox'              => '收件箱',
    'my_tickets'         => '我的工单',
    'unassigned'         => '未分配',
    'trash'              => '回收站',
    'Updates'            => '更新',
    'no_new_updates'     => '没有要更新的',
    'check_for_updates'  => '检查更新',
    'update-version'     => '更新版本',
    'open'               => '打开',
    'inprogress'         => 'Inprogress',
    'inprogress_tickets' => 'Inprogress tickets',
    'closed'             => '关闭',
    'Departments'        => '部门',
    'tools'              => '工具',
    'canned'             => '取消',
    'knowledge_base'     => '基本知识',
    'kb-settings'        => '基础知识设置',
    'loading'            => '加载',
    'ratings'            => '评级',
    'please_rate'        => '请估价:',
    'ticket_ratings'     => '工单等级',
    /*
      |-----------------------------------------------
      |  Ticket
      |-----------------------------------------------
     */
    'ticket_created_successfully'                                        => '工单创建成功',
    'failed_to_create_a_new_ticket'                                      => '创建新工单失败',
    'your_ticket_have_been_closed'                                       => '你的工单已经关闭',
    'your_ticket_have_been_resolved'                                     => '你的工单已经解决',
    'your_ticket_have_been_opened'                                       => '你的工单已经打开',
    'your_ticket_have_been_moved_to_trash'                               => '你的工单被移到回收站了',
    'this_email_have_been_banned'                                        => '这个邮件被禁止了',
    'ticket_updated_successfully'                                        => '工单更新成功',
    'you_have_successfully_replied_to_your_ticket'                       => '回复工单成功',
    'for_some_reason_your_message_was_not_posted_please_try_again_later' => '由于某种原因，你的信息没有被发布。请稍后再试',
    'for_some_reason_your_reply_was_not_posted_please_try_again_later'   => '由于某种原因，你的回复没有被公布。请稍后再试',
    'you_have_unassigned_your_ticket'                                    => '你的工单没有分配',
    'for_some_reason_your_request_failed'                                => '由于某些原因，你的请求失败',
    'trash-delete-ticket'                                                => '永久删除工单',
    'trash-delete-title-msg'                                             => '点击这里永久删除工单.',
    'moved_to_trash'                                                     => '移动选择的工单到回收站.',
    'tickets_have_been_closed'                                           => '关闭选择的工单.',
    'tickets_have_been_opened'                                           => '打开选择的工单.',
    'unable_to_fetch_emails'                                             => '无法获取邮件',
    'reply_content_is_a_required_field'                                  => '回复内容是一个必填字段',
    'internal_content_is_a_required_field'                               => '内部内容是必填字段',
    /*
      |-----------------------------------------------
      |  Profile
      |-----------------------------------------------
     */
    'view-profile'                      => '查看简介',
    'edit-profile'                      => '编辑简介',
    'user_information'                  => '用户信息',
    'time_zone'                         => '时区',
    'phone_number'                      => '电话号码',
    'contact_information'               => '联系信息',
    'Profile-Updated-sucessfully'       => '简介更新成功.',
    'User-profile-Updated-Successfully' => '用户简介更新成功.',
    'User-Created-Successfully'         => '用户创建成功.',
    /*
      |-----------------------------------------------
      |  Dashboard
      |-----------------------------------------------
     */
    'dashboard'         => '看板',
    'line_chart'        => '折线图',
    'statistics'        => '统计',
    'opened'            => '打开的',
    'resolved'          => '解决的',
    'closed'            => '关闭的',
    'deleted'           => '删除的',
    'start_date'        => '开始时间',
    'end_date'          => '结束时间',
    'filter'            => '过滤',
    'report'            => '报告',
    'Legend'            => '图例',
    'total'             => '总计',
    'dashboard_reports' => '控制面板报告',
    /*
      |------------------------------------------------
      |User Page
      |------------------------------------------------
     */
    'user_credentials'                                 => '用户凭证',
    'user_directory'                                   => '用户目录',
    'ban'                                              => '禁止',
    'user'                                             => '用户',
    'users'                                            => '使用者',
    'create_user'                                      => '创建用户',
    'edit_user'                                        => '编辑用户',
    'full_name'                                        => '全名',
    'mobile'                                           => '移动电话',
    'last_login'                                       => '最后一次登录',
    'user_profile'                                     => '用户信息',
    'assign'                                           => '分配',
    'open_tickets'                                     => '打开工单',
    'closed_tickets'                                   => '关闭的工单',
    'deleted_tickets'                                  => '删除的工单',
    'user_created_successfully'                        => '用户创建成功',
    'user_updated_successfully'                        => '用户更新成功',
    'profile_updated_sucessfully'                      => '用户信息更新成功',
    'password_updated_sucessfully'                     => '密码更新成功',
    'password_was_not_updated_incorrect_old_password'  => '密码没有更新。旧密码不正确',
    'the_user_has_been_removed_from_this_organization' => '用户已从这个组织中删除',
    'user_report'                                      => '用户报告',
    'send_password_via_email'                          => '通过电子邮件发送密码',
    'user_send_mail_error_on_user_creation'            => '在向客户发送邮件时发生了一些错误。请检查邮件设置，然后再试一次',
    'country_code'                                     => '国家代码',
    /*
      |------------------------------------------------
      |Organization Page
      |------------------------------------------------
     */
    'organizations'                     => '组织',
    'organization'                      => '组织',
    'organization_list'                 => '组织列表',
    'view_organization_profile'         => '视图组织信息',
    'create_organization'               => '创建组织',
    'account_manager'                   => '客户经理',
    'update'                            => '更新',
    'please_select_an_organization'     => '请选择一个组织',
    'please_select_an_user'             => '请选择一个用户',
    'organization_profile'              => '组织简介',
    'organization-s_head'               => '组织的头',
    'select_department_manager'         => '选择部门经理',
    'select_organization_manager'       => '选择组织管理',
    'users_of'                          => '的用户',
    'organization_created_successfully' => '组织成功创建',
    'organization_can_not_create'       => '无法创建组织',
    'organization_updated_successfully' => '组织成功更新',
    'organization_can_not_update'       => '组织不能更新',
    'organization_deleted_successfully' => '组织成功删除',
    'report_of'                         => '…的报告',
    'ticket_of'                         => '...的工单',
    /*
      |----------------------------------------------
      |  Ticket page
      |----------------------------------------------
     */
    'subject'                                        => '主题',
    'ticket_id'                                      => '工单ID',
    'priority'                                       => '优先级',
    'from'                                           => '来自',
    'last_replier'                                   => '最后回复',
    'assigned_to'                                    => '分配给',
    'last_activity'                                  => '最后一个活动',
    'answered'                                       => '回复',
    'assigned'                                       => '已分配的',
    'create_ticket'                                  => '创建工单',
    'tickets'                                        => '工单',
    'open'                                           => '打开',
    'Ticket_Information'                             => '工单信息',
    'Ticket_Id'                                      => '工单ID',
    'User'                                           => '用户',
    'Unassigned'                                     => '未分配的',
    'unassigned-tickets'                             => '未分配的工单',
    'generate_pdf'                                   => '生成PDF',
    'change_status'                                  => '改变状态',
    'more'                                           => '更多',
    'delete_ticket'                                  => '删除工单',
    'emergency'                                      => '紧急情况',
    'high'                                           => '高级的',
    'medium'                                         => '中等的',
    'low'                                            => '低级的',
    'sla_plan'                                       => 'SLA计划',
    'created_date'                                   => '创建日期',
    'due_date'                                       => '到期日',
    'last_response'                                  => '最后答复',
    'source'                                         => '来源',
    'last_message'                                   => '最后一条消息',
    'reply'                                          => '回复',
    'response'                                       => '响应',
    'reply_content'                                  => '回复内容',
    'attachment'                                     => '附件',
    'internal_note'                                  => '内部注意',
    'this_ticket_is_under_banned_user'               => '这张工单禁止用户使用',
    'ticket_source'                                  => '工单来源',
    'are_you_sure_to_ban'                            => '你确定禁止吗',
    'whome_do_you_want_to_assign_ticket'             => '你要把工单给谁',
    'are_you_sure_you_want_to_surrender_this_ticket' => '你确定要退票吗',
    'add_collaborator'                               => '添加合作者',
    'search_existing_users'                          => '搜索现有的用户',
    'add_new_user'                                   => '增加新用户',
    'search_existing_users_or_add_new_users'         => '搜索存在的用户或增加一个新用户',
    'search_by_email'                                => '通过邮件搜索',
    'list_of_collaborators_of_this_ticket'           => '工单合作者名单',
    'submit'                                         => '提交',
    'max'                                            => '最大文件大小',
    'add_cc'                                         => '添加抄送',
    'recepients'                                     => '收件人',
    'select_a_canned_response'                       => '选择一个回应',
    'assign_to'                                      => '分配给',
    'detail'                                         => '详情',
    'user_details'                                   => '用户详情',
    'ticket_option'                                  => '工单选项',
    'ticket_detail'                                  => '工单详情',
    'Assigned_To'                                    => '分配给',
    'locked-ticket'                                  => '警告!此工单已被锁定 ',
    'minutes-ago'                                    => '……分钟前',
    'access-ticket'                                  => '警告!此工单已被锁定 ',
    'minutes'                                        => ' 分钟',
    'in_minutes'                                     => '在几分钟内',
    'add_another_owner'                              => '添加一个拥有人',
    'user-not-found'                                 => '未找到的用户或用户是不活动的。再次尝试或添加一个新用户.',
    'change-success'                                 => '成功!这张工单的所属人已经换了.',
    'user-exists'                                    => '用户已经存在。试着搜索现有的用户.',
    'valid-email'                                    => '输入一个有效的电子邮件地址.',
    'search_user'                                    => '搜索用户',
    'merge-ticket'                                   => '合并工单',
    'title'                                          => '标题',
    'merge'                                          => '合并',
    'select_tickets'                                 => '选择工单去合并',
    'select-pparent-ticket'                          => '选择父级工单',
    'merge-reason'                                   => '合并理由',
    'no-reason'                                      => '没有合并理由.',
    'get_merge_message'                              => '此工单已合并',
    'ticket_merged'                                  => ' 此工单已合并了吗.',
    'no-tickets-to-merge'                            => '此工单的拥有者已没有工单.',
    'merge-error'                                    => '一段时间后无法处理您的请求.',
    'merge-success'                                  => '工单合并成功.',
    'merge-error2'                                   => '选择工单去合并.',
    'select-tickets-to merge'                        => '选择两个或更多的工单去合并.',
    'different-users'                                => '选择不同用户的工单',
    'clean-up'                                       => '永久删除',
    'hard-delete-success-message'                    => '工单已被永久删除.',
    'overdue'                                        => '过期的',
    'overdue-tickets'                                => '过期的工单',
    'change_owner_for_ticket'                        => '改变工单拥有者',
    /*
      |------------------------------------------------
      |Tools Page
      |------------------------------------------------
     */
    'canned_response'           => '录音响应',
    'create_canned_response'    => '创建录音响应',
    'surrender'                 => '放弃',
    'added_successfully'        => '添加成功',
    'updated_successfully'      => '成功更新',
    'user_deleted_successfully' => '用户删除成功',
    'view'                      => '查看',
    /*
      |-----------------------------------------------
      | Main text
      |-----------------------------------------------
     */
    'copyright'           => '版权',
    'all_rights_reserved' => '版权所有',
    'powered_by'          => '技术支持',
    /*
      |------------------------------------------------
      |Guest-User Page
      |------------------------------------------------
     */
    'issue_summary'             => '问题总结',
    'contact'                   => '联系',
    'issue_details'             => '问题详情',
    'contact_informations'      => '联络信息',
    'contact_details'           => '联系详情',
    'role'                      => '角色',
    'ext'                       => '扩展',
    'profile_pic'               => '照片资料',
    'agent_sign'                => '代理签名',
    'inactive'                  => '不活动的',
    'male'                      => '男性',
    'female'                    => '女性',
    'old_password'              => '旧密码',
    'new_password'              => '新密码',
    'confirm_password'          => '确认密码',
    'gender'                    => '性别',
    'ticket_number'             => '工单编号',
    'content'                   => '内容',
    'edit_template'             => '编辑模板',
    'edit_status'               => '编辑状态',
    'create_status'             => '创建状态',
    'edit_details'              => '编辑细节',
    'edit_templates'            => '编辑模版',
    'activate_this_set'         => '激活设置',
    'show'                      => '显示',
    'no_notification_available' => '没有通知',
    //auto-close workflow
    'close-msg1'                                          => '工单自动关闭后的天数.',
    'no_of_days'                                          => '没几天',
    'close-msg2'                                          => '启用自动关闭工作流?',
    'enable_workflow'                                     => '启用工作流',
    'send_email_to_user'                                  => '给用户发送邮件',
    'close-msg3'                                          => '选择要关闭工单的状态.',
    'close-msg4'                                          => '发送电子邮件给用户自动关闭工单?',
    'edit_status'                                         => '编辑状态',
    'list_of_status'                                      => '状态列表',
    'status_settings'                                     => '状态设置',
    'icon_class'                                          => '图标类',
    'close_ticket_workflow'                               => '关闭工单工作流',
    'ratings_settings'                                    => '等级设置',
    'notification'                                        => '通知',
    'status_has_been_updated_successfully'                => '状态更新成功',
    'status_has_been_created_successfully'                => '状态创建成功',
    'status_has_been_deleted'                             => '状态已被删除',
    'you_cannot_delete_this_status'                       => '状态无法删除',
    'you_have_deleted_all_the_read_notifications'         => '您已经删除了所有读取的通知',
    'you_have_deleted_all_the_notification_records_since' => '您已经删除了所有的通知记录 ',
    'ratings_updated_successfully'                        => '评级更新成功',
    'ratings_can_not_be_created'                          => '评级不能被创建',
    'successfully_created_this_rating'                    => '成功创建了这个评级',
    'rating_deleted_successfully'                         => '评级成功删除',
    //status msg
    'status_msg1'                          => '如果您选择“是”，将向用户发送电子邮件通知.',
    'notify_user'                          => '通知用户此状态?',
    'deleted_status'                       => '这是已删除的工单状态吗?',
    'resolved_status'                      => '这是已解决的工单状态吗?',
    'status_msg3'                          => '选择"是",工单状态将会设置为已解决.',
    'status_msg2'                          => '选择"是",工单状态将会设置为已删除.',
    'rating-msg2'                          => '选择一个部门将这个等级限制在一个特定部门的门票或聊天。如果没有选择部门，评分将在所有部门中显示.',
    'rating-msg3'                          => '如果您选择是，用户可以修改评级.',
    'rating_restrict'                      => '将评级限制在一个部门',
    'rating_change'                        => '允许用户更改评级?',
    'security_msg1'                        => '当用户(主机)被锁定时显示的消息.',
    'security_msg2'                        => '用户在主机/用户或计算机被锁在系统之外之前的登录尝试次数。设置为0以记录错误的登录尝试，而不锁定主机/用户.',
    'security_msg3'                        => '主机或用户在登录失败后被禁止登录本网站的时间.',
    'max_attempt'                          => '每个主机/用户的最大登录尝试',
    'rating-msg1'                          => '可以给出的最大额定值。例如，如果选择了5，那么最低的评级将是1和最高的5.',
    'enter_no_of_days'                     => '进入没有几天',
    'template-types'                       => '模板类型',
    'close-workflow'                       => '关闭工单工作流',
    'template'                             => '，模版',
    'rating_label'                         => '等级标签',
    'display_order'                        => '显示排序',
    'rating_scale'                         => '等计量表',
    'rating_area'                          => '等级区域',
    'modify'                               => '更改',
    'rating_name'                          => '评级名字',
    'add_user_to_this_organization'        => '增加用户到组织',
    'Tickets_of'                           => 'Tickets of',
    'security'                             => '安全',
    'security_settings'                    => '安全设置',
    'lockouts'                             => '锁定',
    'security_settings_saved_successfully' => '安全设置保存成功',
    'manage_status'                        => '管理状态',
    'notifications'                        => '通知',
    'auto_close_workflow'                  => '自动关闭工作流',
    'close_ticket_workflow_settings'       => '关闭工单工作流设置',
    'successfully_saved_your_settings'     => '成功保存你的设置',
    /*
      |------------------------------------------------
      |   Notification Settings Pages
      |------------------------------------------------
     */
    'notification_settings'                       => '通知设置',
    'delete_noti'                                 => '删除所有读过的通知?',
    'noti_msg1'                                   => '删除通知日志的天数',
    'noti_msg2'                                   => '您可以输入要删除的数据库日志的天数，通知的历史将从指定的日期被删除.',
    'del_all_read'                                => '删除所有已读的',
    'You_have_deleted_all_the_read_notifications' => '您已经删除了所有读取的通知',
    'view_all_notifications'                      => '查看所有通知',
    /*
      |------------------------------------------------
      |   Error Pages
      |------------------------------------------------
     */
    'not_found'                                       => '未发现',
    'oops_page_not_found'                             => 'Oops! 页面未发现',
    'we_could_not_find_the_page_you_were_looking_for' => '我们找不到你要找的那一页',
    'internal_server_error'                           => '内部服务器错误',
    'be_right_back'                                   => '马上回来',
    'sorry'                                           => '抱歉',
    'we_are_working_on_it'                            => '我们正在改善',
    'category'                                        => '分类',
    'addcategory'                                     => '增加分类',
    'allcategory'                                     => '所有分类',
    'article'                                         => '文章',
    'articles'                                        => '文章',
    'addarticle'                                      => '增加文章',
    'allarticle'                                      => '所有文章',
    'pages'                                           => '页数',
    'addpages'                                        => '增加页数',
    'allpages'                                        => '所有页面',
    'widgets'                                         => '小工具',
    'widget-settings'                                 => '小工具设置',
    'footer1'                                         => '页脚 1',
    'footer2'                                         => '页脚 2',
    'footer3'                                         => '页脚 3',
    'footer4'                                         => '页脚 4',
    'sidewidget1'                                     => '侧边小部件 1',
    'sidewidget2'                                     => '侧边小部件 2',
    'comments'                                        => '评论',
    'comments-list'                                   => '评论列表',
    'settings'                                        => '设置',
    'parent'                                          => '父级',
    'description'                                     => '描述',
    'enter_the_description'                           => '输入描述',
    'publish'                                         => '发布',
    'publish_immediately'                             => '立刻发布',
    'published'                                       => '已发布',
    'draft'                                           => '草稿',
    'create_a_category'                               => '创建分类',
    'add'                                             => '增加',
    'social'                                          => '社交',
    'social-widget-settings'                          => '社交小工具设置',
    'comment'                                         => '评论',
    'not_published'                                   => '不发布',
    'numberofelementstodisplay'                       => '要显示的元素的数量',
    //======================================
    'language'                                                                 => '语言',
    'save'                                                                     => '保存',
    'create'                                                                   => '创建',
    'dateformat'                                                               => '日期格式',
    'slug'                                                                     => 'Slug',
    'read_more'                                                                => '查看全部',
    'view_all'                                                                 => '查看所有',
    'categories'                                                               => '分类',
    'need_more_support'                                                        => '需要更多支持',
    'if_you_did_not_find_an_answer_please_raise_a_ticket_describing_the_issue' => '如果你没有找到答案，请提交工单附加这个问题的描述',
    'have_a_question?_type_your_search_term_here'                              => '有问题吗?在这里输入搜索词...',
    'search'                                                                   => '搜索',
    'frequently_asked_questions'                                               => '常见问题',
    'leave_a_reply'                                                            => '留下回复',
    'post_message'                                                             => '发布讯息',
    /*
      |--------------------------------------------------------------------------------------
      |  Client Panel [English(en)]
      |--------------------------------------------------------------------------------------
      | The following language lines are used in all Agent Panel related issues to translate
      | some words in view to English. You are free to change them to anything you want to
      | customize your views to better match your application.
      |
     */
    'home'                                                                             => '主页',
    'submit_a_ticket'                                                                  => '提交工单',
    'my_profile'                                                                       => '个人信息',
    'log_out'                                                                          => '退出',
    'forgot_password'                                                                  => '忘记密码',
    'create_account'                                                                   => '创建账号',
    'you_are_here'                                                                     => '你在这',
    'have_a_ticket'                                                                    => '有个工单',
    'check_ticket_status'                                                              => '检查工单状态',
    'choose_a_help_topic'                                                              => '选择帮助主题',
    'ticket_status'                                                                    => '工单状态',
    'post_comment'                                                                     => '文章评论',
    'plugin'                                                                           => '插件',
    'edit_profile'                                                                     => '编辑资料',
    'Send'                                                                             => '发送',
    'no_article'                                                                       => '没有文章',
    'profile_settings'                                                                 => '资料设置',
    'please_fill_all_required_feilds'                                                  => '请填充所有必填字段.',
    'successfully_replied'                                                             => '回复成功',
    'please_fill_some_data'                                                            => '请填充一些数据!',
    'profile_updated_sucessfully'                                                      => '资料更新成功',
    'password_updated_sucessfully'                                                     => '密码更新成功',
    'password_was_not_updated_incorrect_old_password'                                  => '密码未更新，请纠正你的旧密码',
    'there_is_no_such_ticket_number'                                                   => '没有这样的工单编号',
    "email_didn't_match_with_ticket_number"                                            => '邮件无法匹配到工单',
    'we_have_sent_you_a_link_by_email_please_click_on_that_link_to_view_ticket'        => '我们通过邮箱发送你们一个邮件，点击连接查看工单',
    'no_records_on_publish_time'                                                       => '在发布时间内没有记录',
    'your_details_send_to_system'                                                      => '你的资料发送到系统',
    'your_details_can_not_send_to_system'                                              => '你的资料没有发送到系统',
    'your_comment_posted'                                                              => '你评论了文章',
    'sorry_not_processed'                                                              => '抱歉没进程',
    'profile_updated_sucessfully'                                                      => '资料更新成功',
    'password_was_not_updated'                                                         => '密码未更新',
    'sorry_your_ticket_token_has_expired_please_try_to_resend_the_ticket_link_request' => '抱歉，你的工单令牌失效! 请尝试重新发送连接请求',
    'sorry_you_are_not_allowed_token_expired'                                          => '抱歉，不允许，令牌实效!',
    'thank_you_for_your_rating'                                                        => '谢谢你的评价!',
    'your_ticket_has_been'                                                             => 'Your ticket has been',
    'failed_to_send_email_contact_administrator'                                       => '邮件发送失败，请联系系统管理员',
    /*
     * |---------------------------------------------------------------------------------------
     * |API settings
     * |----------------------------------------------------------------------------------
     * |The following lanuage line used to get english traslation of api settings in admin panel
     * |
     * |
     */
    'webhooks'                         => 'Webhooks',
    'enter_url_to_send_ticket_details' => '输入url发送邮件细节',
    'api'                              => 'API',
    'api_key'                          => 'API key',
    'api_key_mandatory'                => 'API key 托管',
    'api_configurations'               => 'API ',
    'generate_key'                     => '生成钥匙',
    'api_settings'                     => 'API 设置',
    /*
     * -----------------------------------------------------------------------------
     * Error log and debugging settings
     * --------------------------------------------------------------------------
     *
     */
    'error-debug'                        => '错误日志与debug',
    'debug-options'                      => 'debug 选项',
    'view-logs'                          => '查看错误日志',
    'not-authorised-error-debug'         => '你无权访问URl',
    'error-debug-settings'               => '错误与debug设置',
    'debugging'                          => 'debug模式',
    'bugsnag-debugging'                  => '发送应用崩溃报告，帮助瓢虫改善Faveo',
    'error-debug-settings-saved-message' => '错误与debug配置保存成功',
    'error-debug-settings-error-message' => '你没做任何改变.',
    'error-logs'                         => '错误日志',
    /* ---------------------------------------------------------------------------------------
     * Latest update 16-06-2016
     * -----------------------------------------------------------------------------------
     */
    'that_email_is not_available_in_this_system' => '无效的邮件在系统中',
    'use_subject'                                => '使用主题',
    'reopen'                                     => '重新打开',
    'invalid_attempt'                            => '无效的尝试',
    /* ---------------------------------------------------------------------------------------
     * Latest update 27-07-2016
     * -----------------------------------------------------------------------------------
     */
    'queue'  => '队列',
    'queues' => '队列',
    /*     * -------------------------------------------------------------------------------------------------
     * OTP  messages body to send to user while registering, resetting passwords
     * --------------------------------------------------------------------------------------------------
     */
    'hello'                   => 'Hello',
    'reset-link-msg'          => ",\r\n这里是重新设置密码连接.\r\n",
    'otp-for-your'            => ",\r\n你的OTP",
    'account-verification-is' => '帐户验证',
    'extra-text'              => ".\r\n你能登陆你的账户用OTP或点击我们发送到你邮箱的这个连接.",
    'otp-not-sent'            => '在发送OTP时，我们遇到一些问题，请稍后尝试.',
    /*     * -------------------------------------------------------------------------------------------
     * Ticket number settings 03-08-2016
     * ------------------------------------------------------------------------------------------
     */
    'format'               => '格式',
    'ticket-number-format' => '此设置用于生成票号。使用要放置数字的哈希符号(\' # \')和要放置字符的美元符号(\' $ \')。任何其他文本的数字格式将被保留. ',
    'ticket-number-type'   => '选择一个序列，从中得到新的票号。系统默认有一个递增序列和一个随机序列',
    /*     * ----------------------------------------------------------------------------------------------------
     * Social media integration
     * ---------------------------------------------------------------------------------------------------------
     */
    'client_id'     => '客户id',
    'client_secret' => '客户密钥',
    'redirect'      => '跳转URL',
    'details'       => '详情',
    'social-media'  => '社会媒体',
    /*     * ----------------------------------------------------------------------------------------------
     * Report
     * ----------------------------------------------------------------------------------------------
     */
    'report'              => '报告',
    'Report'              => '报告',
    'start_date'          => '开始日期',
    'end_date'            => '结束日期',
    'select'              => '选择',
    'generate'            => '生成',
    'day'                 => '天',
    'week'                => '周',
    'month'               => '月',
    'Currnet_In_Progress' => '当前进程',
    'Total_Created'       => '共计创建',
    'Total_Reopened'      => '共计重新打开',
    'Total_Closed'        => '共计关闭',
    'tabular'             => '表格式的',
    'reopened'            => '重新打开',
    /* ---------------------------------------------------------------------------------------
     * Ticket Priority
     * -----------------------------------------------------------------------------------
     */
    'ticket_priority'                                           => '工单优先级',
    'priority'                                                  => '优先级',
    'priority_desc'                                             => '优先级描述',
    'priority_urgency'                                          => '优先级紧急状况',
    'priority_id'                                               => '优先级 Id',
    'priority_color'                                            => '优先级颜色',
    'ispublic'                                                  => '是否发布',
    'is_default'                                                => '默认',
    'create_ticket_priority'                                    => '创建工单优先级',
    'agent_notes'                                               => '代理说明',
    'select_priority'                                           => '选择优先级',
    'normal'                                                    => '常规',
    'ispublic'                                                  => '可见性',
    'make-default-priority'                                     => '设置默认优先级',
    'priority_successfully_created'                             => '优先级创建成功',
    'priority_successfully_updated'                             => '优先级更新成功',
    'delete_successfully'                                       => '删除成功',
    'user_priority_status'                                      => '用户优先级状态',
    'current'                                                   => '当前:',
    'active_user_can_select_the_priority_while_creating_ticket' => '活动用户可以在创建票据时选择优先级',
    /* --------------------------------------------------------------------------------------------
     * Approval Updated
     * --------------------------------------------------------------------------------------------
     */
    'approval'                               => '批准',
    'approval_tickets'                       => '提准工单',
    'approve'                                => '批准',
    'approval_request'                       => '批准请求',
    'approvel_ticket_list'                   => '批准工单列表',
    'approval_settings'                      => '批准设置',
    'close_all_ticket_for_approval'          => '关闭所有批准的工单',
    'approval_settings-created-successfully' => '批准设置创建成功',
    /* --------------------------------------------------------------------------------------------
     * Followup Updated
     * --------------------------------------------------------------------------------------------
     */
    'followup'              => '跟踪',
    'followup_tickets'      => '追踪工单',
    'followup_Notification' => '后续通知',
    /*
     * --------------------------------------------------------------------------------------------
     * Updated 6-9-2016
     * ---------------------------------------------------------------------------------------
     */
    'not-available' => '无法使用',
    /* --------------------------------------------------------------------------------------------
     * User Module
     * --------------------------------------------------------------------------------------------
     */
    'agent_report'                                                 => '代理报告',
    'assign_tickets'                                               => '分配工单',
    'delete_agent'                                                 => '删除代理',
    'delete_user'                                                  => '删除用户',
    'confirm_deletion'                                             => 'Confirm deletion',
    'delete_all_content'                                           => '删除所有内容',
    'agent_profile'                                                => '代理文档',
    'change_role_to_admin'                                         => '改变角色到管理',
    'change_role_to_user'                                          => '改变角色到用户',
    'change_role_to_agent'                                         => '改变角色到代理',
    'change_password'                                              => '改变密码',
    'role_change'                                                  => '角色改变',
    'password_generator'                                           => '密码生成器',
    'department'                                                   => '部门',
    'duetoday'                                                     => '今天到期',
    'today-due_tickets'                                            => '今天的到期工单',
    'password_change_successfully'                                 => '密码修改成功',
    'role_change_successfully'                                     => '角色删除成功',
    'user_delete_successfully'                                     => '用户删除成功',
    'agent_delete_successfully'                                    => '代理删除成功',
    'select_another_agent'                                         => '选择另一个代理',
    'agent_delete_successfully_and_ticket_assign_to_another_agent' => '代理删除成功并且已分配给另一代理',
    'deleted_user'                                                 => '删除用户',
    'deleted_user_directory'                                       => '删除用户目录',
    'restore'                                                      => '修复',
    'user_restore_successfully'                                    => '用户修复成功',
    /*     * * updates 7-6-2018** */
    'apply' => 'Apply',
    /* updates 7-6-2018 * */
    'sort-by'                      => '排序',
    'created-at'                   => '创建在',
    'or'                           => '或',
    'activate'                     => '激活',
    'system-email-not-configured'  => '我们无法处理电子邮件请求，因为系统没有配置电子邮件发送。请联系并报告系统管理员.',
    'assign-ticket'                => '分配工单',
    'can-not-inactive-group'       => '不能使组无效，因为组中分配了代理。请将这些代理分配给另一组，然后再试一次.',
    'internal-note-has-been-added' => '内注已增加到工单',
    'active-users'                 => '激活用户',
    'deleted-users'                => '删除用户',
    'view-option'                  => '查看选项',
    'accoutn-not-verified'         => '用户账号未验证',
    'enabled'                      => '启用',
    'disabled'                     => '禁用',
    'user-account-is-deleted'      => '用户账号删除成功.',
    'restore-user'                 => '修复用户账号',
    'delete-account-caution-info'  => '请注意，这个帐户可能在系统中仍然有打开的工单.',
    'reply-can-not-be-empty'       => '回复是空的，请输入回复消息.',
    //update 18-12-2016
    'account-created-contact-admin-as-we-were-not-able-to-send-opt' => '你的账户创建成功.请联系管理员去激活.',
    //update 19-12-2016
    'only-agents'    => '代理用户',
    'only-users'     => '客户端用户',
    'banned-users'   => '禁用的用户',
    'inactive-users' => '激活的用户',
    'all-users'      => '所有用户',
    'search'         => '搜索...',
    //update 21-12-2016
    'selected-user-is-already-the-owner' => '选择的用户已经是工单的拥有者.',
    //update 1-2-2017
    'system-outgoing-incoming-mail-not-configured' => '你没有配置系统邮件. Faveo 无法发送邮件给用户.',
    'confihure-the-mail-now'                       => '点击这里配置邮箱.',
    'system-mail-not-configured-agent-message'     => '系统传入和传出的电子邮件设置没有配置。请联系管理.',
    // arindam-14.2.2017
    // sla
    'min'    => '分',
    'hours'  => '时',
    'days'   => '天',
    'months' => '月',
    'year'   => '年',
    // department change
    'change_department'                      => '改变部门',
    'ticket_department_successfully_changed' => '成功改变工单部门',
    'select_another_department'              => '选择另一个部门',
    // status
    /*     * ----------------------------------------------------------------
     * Status
     * ----------------------------------------------------------------
     */
    'client'               => '客户',
    'send_email'           => '发送邮件',
    'visibility_to_client' => '可见客户',
    'purpose_of_status'    => '目标状态',
    'status_to_display'    => '展示状态',
    'icon_color'           => '图表颜色',
    /*     * --------------------------------------------------------------------------------------------
     * Status
     * --------------------------------------------------------------------------------------------
     */
    'visible_to_client'                                                               => '可见客户',
    'icon'                                                                            => '图表',
    'none'                                                                            => '没有',
    'allow_client'                                                                    => '允许客户',
    'if_yes_status_name_will_be_displayed'                                            => '如果是，将显示状态名',
    'if_yes_then_clients_can_choose_this_status'                                      => '如果是，客户能选择这个状态',
    'purpose_of_status_will_perform_the_action_to_be_applied_on_the_status_selection' => '状态的目的将执行应用于状态选择的操作',
    'this_message_will_be_displayed_in_the_thread_as_internal_note'                   => '此消息将作为内部通知显示在线程中',
    'make_system_default_for_selected_purpose'                                        => '为选择的目标设置系统默认值',
    'this_status_will_be_displayed_to_client_if_visibility_of_client_chosen_no'       => '状态将会显示到客户端，如果客户选择的是no',
    'tick_who_all_to_send_notification'                                               => '发送通知',
    'Default'                                                                         => '默认',
    'unable_to_change_the_purpose_of_status_there_are_tickets_with_this_status'       => '不能改变工单状态，正在被使用.',
    'you_cannot_delete_a_default_ticket_status'                                       => '不能删除默认的状态',
    'associated_tickets_moved_to_default_status'                                      => '相关工单已移动到默认状态',
    'status_deleted_successfully'                                                     => '状态删除成功',
    'have_been_marked_as'                                                             => '已被标记作为',
    'have_been_deleted_forever'                                                       => '永久删除',
    'related_tickets_moved_to_default_status'                                         => '相关工单已移动到默认状态',
    //updates 22-2-2017
    'invalid-date-range' => '无效的日期范围',
    //updates 14-4-2017
    'notification.priority.update'       => '更新被 :agent - 排序改变从 :old 到 :new',
    'notification.source.update'         => '更新被 :agent - 来源改变从 :old 到 :new',
    'notification.title.update'          => '更新被 :agent - 标题改变从 :old 到 :new',
    'notification.helptopic.update'      => '更新被 :agent - 帮助改变从 :old 到 :new',
    'notification.sla.update'            => '更新被 :agent - Sla改变从 :old 到 :new',
    'notification.status.update'         => '更新被 :agent - 状态改变从 :old 到 :new',
    'notification.assign.update'         => '分配被 :agent - 工单 分配给 :new',
    'notification.user.update'           => '工单所有权更改被 :agent - 从 :old 到 :new',
    'notification.department.update'     => '更新被 :agent - 部门改变从 :old 到 :new',
    'created.ticket'                     => '一个新工单 :subject 已经创建',
    'mode'                               => '模式',
    'new_internal_activity_alert'        => '新网络激活提醒',
    'sms'                                => 'Sms',
    'agent'                              => '代理',
    'all_department_manager'             => '所有部门管理',
    'all_team_lead'                      => '所有团队领导',
    'registration_notification'          => '登记通知',
    'reply_notification'                 => '回复通知',
    'notification.update'                => ':model 设置为 :new 从 :old',
    'notification.update.inapp'          => ':model 设置为 :new 从 :old 在 <b>:ticket</b>',
    'notification.assigned'              => '已分配工单到 :new',
    'notification.assigned.inapp'        => '已分配工单 <b>:ticket</b> 到 :new',
    'notification.assigned.myself'       => '工单已分配给他自己',
    'notification.assigned.myself.inapp' => ' 已经分配<b>:ticket</b> 给他自己',
    'notification.duedate'               => '工单 :model 在 :new',
    'notification.duedate.inapp'         => '工单 :model 在 :new',
    'notification.note'                  => '新 :model 已增加 - :new',
    'notification.note.inapp'            => '增加 :model 在 <b>:ticket</b> - :new',
    'agent_reply'                        => '代理回复',
    'client_reply'                       => '客户端回复',
    'new-user-register'                  => '已注册',
    'reply.notification'                 => '这个回答在 <b>:title</b>',
    'custom-format'                      => '自定义个是',
    'assigned_agent'                     => '指定代理',
    'in_app_system'                      => '通知',
    'new_ticket_confirmation_alert'      => '新工单确认',
    'registration_verification'          => '注册验证',
    'client'                             => '哭护短',
    'sla_alert'                          => 'SLA 弹出',
    // mobile OTP verification screen
    'verification_channel'           => '验证频道',
    'verify-mobile'                  => '验证手机号码',
    'send_otp'                       => '发送 OTP',
    'enter-otp-message-to-users'     => '我们已经发送OTP到你的手机,请验证你的OTP.',
    'edit_number'                    => '编辑号码',
    'enter_different_number'         => '输入新号码或更新',
    'otp_sending_message'            => '稍等，我们正在发送OTP到你的电话',
    'mobile_number_already_verified' => '电话号码已被另一个用户关联.',
    'error-sms-service-not-active'   => '失败去发送OTP作为 SMS 服务无效.',
    'otp-can-not-be-verified'        => '发送OTP失败，请稍后重试',
    'otp-sent-successfully'          => 'OTP 发送成功',
    //updated 7-6-2018
    'filter_tickets'                   => '过滤工单',
    'account_active'                   => '激活账号',
    'user_account_not_active'          => '用户账号未激活',
    'user_account_is_active'           => '用户账户是激活的',
    'user_has_not_verified_email'      => '用户没有验证邮箱地址',
    'user_email_is_verified'           => '用户邮箱地址已验证',
    'user_mobile_is_verified'          => '用户联系电话已验证',
    'user_has_not_verified_email'      => '用户未验证联系电话',
    'email_verify'                     => '邮箱验证',
    'mobile_verify'                    => '手机验证',
    'status_updated_successfully'      => '状态更新成功',
    'ticket_type'                      => '工单类型',
    'plugin_updated_successfully'      => '差价更新成功',
    'on'                               => '打开',
    'off'                              => '关闭',
    'new_requester_added'              => '增加新请求',
    'list of ticket types'             => '工单类型列表',
    'ticket_type_name'                 => '类型名称',
    'type_desc'                        => '类型描述',
    'create_ticket_type'               => '创建工单类型',
    'create_new_ticket_type'           => '创建新的工单类型',
    'ticket_type_saved_successfully'   => '工单类型保存成功',
    'make-default-type'                => '设置默认类型',
    'edit_ticket_type'                 => '编辑工单类型',
    'ticket_type_updated_successfully' => '工单类型更新成功',
    'ticket_type_deleted_successfully' => '工单类型删除成功',
    'create_new_ticket'                => '创建工单',
    //updated 10-10-2023
    'what_should_be_done_with_content_owned_by_this_user'  => '应如何处理该用户拥有的内容',
];
