<?php

return [
    /*
      |----------------------------------------------------------------------------------------
      | Authentication Pages [English(en)]
      |----------------------------------------------------------------------------------------
      |
      | The following language lines are used in all authentication related issues to translate
      | some words in view to English. You are free to change them to anything you want to
      | customize your views to better match your application.
      |
     */

    /*
      |--------------------------------------
      |   Error
      |--------------------------------------
     */
    'success'        => 'Erfolgreich',
    'fails'          => 'Fehler',
    'alert'          => 'Alarm',
    'required-error' => 'Bitte alle notwendigen Felder ausfüllen',
    'invalid'        => 'Falsche Benutzer-ID oder Passwort',
    /*
      |--------------------------------------
      |   Login Page
      |--------------------------------------
     */
    'Login_to_start_your_session' => 'Einloggen um Ihre Sitzung zu starten',
    'login'                       => 'Anmeldung',
    'remember'                    => 'Erinnere dich an mich',
    'signmein'                    => 'Melde mich an',
    'iforgot'                     => 'Ich habe mein Passwort vergessen',
    'email_address'               => 'E-Mail-Addresse',
    'password'                    => 'Passwort',
    'woops'                       => 'Whoops!',
    'theirisproblem'              => 'Es gab einige Probleme mit Ihrer Eingabe.',
    'e-mail'                      => 'E-Mail',
    'reg_new_member'              => 'Registrieren für eine neue Mitgliedschaft',
    /*
      |--------------------------------------
      |   Register Page
      |--------------------------------------
     */
    'registration'                => 'Registrierung',
    'full_name'                   => 'Vollständiger Name',
    'firstname'                   => 'Vorname',
    'lastname'                    => 'Familienname',
    'profilepicture'              => 'Profilbild',
    'oldpassword'                 => 'Altes Passwort',
    'newpassword'                 => 'Neues Passwort',
    'retype_password'             => 'Passwort erneut eingeben',
    'i_agree_to_the'              => 'Ich stimme dem zu',
    'terms'                       => 'Bedingungen',
    'register'                    => 'Neu registrieren',
    'i_already_have_a_membership' => 'Ich habe bereits eine Mitgliedschaft',
    /*
      |--------------------------------------
      |   Reset Password Page
      |--------------------------------------
     */
    'reset_password' => 'Passwort zurücksetzen',
    /*
      |--------------------------------------
      |   Forgot Password Page
      |--------------------------------------
     */
    'i_know_my_password'            => 'Ich weiß mein Passwort',
    'recover_passord'               => 'Passwort wiederherstellen',
    'send_password_reset_link'      => 'Passwort Reset Link senden',
    'enter_email_to_reset_password' => 'E-Mail angeben um Passwort zurücksetzen',
    'link'                          => 'Link',
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
    'admin_panel' => 'Administrationsmenü',
    /*
      |--------------------------------------
      |  Emails Create Page
      |--------------------------------------
     */
    'emails'                         => 'E-Mails',
    'incoming_emails'                => 'Eingehende E-Mails',
    'reuired_authentication'         => 'Erforderliche Authentifizierung',
    'fetching_email_via_imap'        => 'Abrufen von E-Mail über IMAP',
    'create_email'                   => 'E-Mail erstellen',
    'email_address'                  => 'E-Mail-Addresse',
    'email_name'                     => 'E-Mail-Name',
    'help_topic'                     => 'Hilfethema',
    'auto_response'                  => 'Automatische Antwort',
    'host_name'                      => 'Hostname',
    'port_number'                    => 'Portnummer',
    'mail_box_protocol'              => 'Mailbox-Protokoll',
    'authentication_required'        => 'Authentifizierung erforderlich',
    'yes'                            => 'Ja',
    'no'                             => 'Nein',
    'header_spoofing'                => 'Header Spoofing',
    'allow_for_this_email'           => 'Zulassen für diese E-Mail',
    'imap_config'                    => 'IMAP-Konfiguration',
    'email_information_and_settings' => 'E-Mail-Informationen und Einstellungen',
    'incoming_email_information'     => 'Eingehende E-Mail-Informationen',
    'outgoing_email_information'     => 'Ausgehende E-Mail-Informationen',
    'new_ticket_settings'            => 'Neue Ticketeinstellungen',
    'protocol'                       => 'Protokoll',
    'fetching_protocol'              => 'Protokoll holen',
    'transfer_protocol'              => 'Übertragungsprotokoll',
    'from_name'                      => 'Von Name',
    'add_an_email'                   => 'Fügen Sie eine E-Mail hinzu',
    'edit_an_email'                  => 'Bearbeiten Sie eine E-Mail',
    'disable_for_this_email_address' => 'Deaktivieren Sie diese E-Mail-Adresse',
    /*
      |--------------------------------------
      |  Ban Emails Create Page
      |--------------------------------------
     */
    'ban_lists'  => 'Sperrlisten',
    'ban_email'  => 'E-Mail sperren',
    'banlists'   => 'Sperrlisten',
    'ban_status' => 'Verbotsstatus',
    /*
      |--------------------------------------
      |  Templates Index Page
      |--------------------------------------
     */
    'templates'       => 'Vorlagen',
    'template_set'    => 'Vorlagensätze',
    'create_template' => 'Vorlage erstellen',
    'edit_template'   => 'Vorlage bearbeiten',
    'in_use'          => 'In Benutzung',
    //Template Description
    'Create ticket agent'       => 'Benachrichtigungs-E-Mail, die beim Erstellen des Tickets an Agent & Admin gesendet wird',
    'Assign ticket'             => 'Ticket, das einem Agenten zugewiesen wurde',
    'Create ticket'             => 'E-Mail, die an den Client gesendet wird, damit die Nachricht erfolgreich erstellt werden kann',
    'Check ticket'              => 'Wenn ein Kunde ein Ticket über das Kundenportal prüfen möchte, wird ein Link an den Kunden gesendet. Über diesen Link kann der Kunde Ticketdetails mit Ticketnummer anzeigen, ohne sich beim System anzumelden',
    'Ticket reply agent'        => 'Sobald der Client auf das Ticket antwortet, wird eine Benachrichtigung an die E-Mail-Adresse des Agenten gesendet',
    'Registration notification' => 'Passwort und Benutzername werden bei der ersten Registrierung per E-Mail gesendet',
    'Reset password'            => 'E-Mail mit Passwort zurücksetzen Link',
    'Error report'              => 'Fehlermeldung',
    'Ticket creation'           => 'Erste Benachrichtigung, die vom System über die Ticket-Erstellung an den Client gesendet wird',
    'Ticket reply'              => 'Eine Antwort des Agenten auf ein Ticket, E-Mail-Benachrichtigung wird an Client und Mitarbeiter gesendet',
    'Close ticket'              => 'Mail beim Schließen eines Tickets an den Kunden gesendet',
    'Create ticket by agent'    => 'Ein Agent erstellt ein Ticket für den Client im Namen des Clients',
    /*
      |--------------------------------------
      |  Templates Create Page
      |--------------------------------------
     */
    'template_set_to_clone' => 'Vorlage zum Klonen',
    'language'              => 'Sprache',
    /*
      |--------------------------------------
      |  Diagnostics Page
      |--------------------------------------
     */
    'diagnostics' => 'Fehlerdiagnose',
    'from'        => 'Von',
    'to'          => 'An',
    'subject'     => 'Betreff',
    'message'     => 'Nachricht',
    'send'        => 'Senden',
    /*
      |----------------------------------------------------------------------------------------
      | Settings Pages [English(en)]
      |----------------------------------------------------------------------------------------
      |
      | Die folgenden Sprachzeilen werden in allen Problemen mit der Einstellung verwendet,
      | verwendet um einige Wörter ins Deutsche zu übersetzen. Es steht Ihnen frei, Sie so zu ändern
      | dass Sie IHre Ansichten an Ihre Anforderungen anpassen können.
      |
     */

    /*
      |--------------------------------------
      |   Company Settings Page
      |--------------------------------------
     */
    'company'      => 'Unternehmen',
    'website'      => 'Webseite',
    'phone'        => 'Telefon',
    'address'      => 'Adresse',
    'landing'      => 'Landing Seite',
    'offline'      => 'Offline Seite',
    'thank'        => 'Danke Seite',
    'logo'         => 'Logo',
    'save'         => 'Speichern',
    'delete-logo'  => 'Logo löschen',
    'click-delete' => 'Hier klicken, um zu löschen',
    /*
      |--------------------------------------
      |   System Settings Page
      |--------------------------------------
     */
    'system'             => 'System',
    'online'             => 'Online',
    'offline'            => 'Offline',
    'name/title'         => 'Name/Titel',
    'pagesize'           => 'Seitengröße',
    'url'                => 'URL',
    'default_department' => 'Standardabteilung',
    'loglevel'           => 'Protokollebene',
    'purglog'            => 'Logs löschen',
    'nameformat'         => 'Namensformatierung',
    'timeformat'         => 'Zeitformat',
    'date'               => 'Datum',
    'dateformat'         => 'Datumsformat',
    'date_time'          => 'Datum und Zeit Format',
    'day_date_time'      => 'Tag, Datum und Uhrzeit Format',
    'timezone'           => 'Standardzeitzone',
    'api'                => 'Api',
    'api_key'            => 'API-Schlüssel',
    'api_key_mandatory'  => 'Api-Schlüssel obligatorisch',
    'api_configurations' => 'API-Konfigurationen',
    'generate_key'       => 'Schlüssel generieren',
    /*
      |--------------------------------------
      |   Email Settings Page
      |--------------------------------------
     */
    'email'                               => 'E-Mail-Addresse',
    'default_template'                    => 'Standardvorlagensatz:',
    'default_system_email'                => 'Standard-System-E-Mail:',
    'default_alert_email'                 => 'Standard-Benachrichtigungs-E-Mail:',
    'admin_email'                         => 'Admin-E-Mail-Adresse:',
    'email_fetch'                         => 'E-Mail-Abruf:',
    'enable'                              => 'Aktivieren',
    'default_MTA'                         => 'Standard-MTA',
    'fetch_auto-corn'                     => 'Holen Sie auf Auto-Cron',
    'strip_quoted_reply'                  => 'Zitierte Antworten ausschneiden',
    'reply_separator'                     => 'Antwort Trennzeichen',
    'accept_all_email'                    => 'Akzeptiere alle E-Mails',
    'accept_email_unknown'                => 'Akzeptiere E-Mail von unbekannten Benutzern',
    'accept_email_collab'                 => 'E-Mail-Mitarbeiter akzeptieren',
    'automatically_and_collab_from_email' => 'Fügen Sie Mitarbeiter automatisch aus E-Mail-Feldern hinzu',
    'default_alert_email'                 => 'Standard-Benachrichtigungs-E-Mail',
    'attachments'                         => 'Anlagen',
    'email_attahment_user'                => 'E-Mail-Anhänge an den Benutzer',
    'cron_notification'                   => 'Benachrichtigungscron aktivieren',
    'cron'                                => 'Job Scheduler',
    'crone-url-message'                   => "These are Faveo's Job Scheduler(cron job) url for your system.",
    'clipboard-copy-message'              => 'In die Zwischenablage kopiert.',
    'click'                               => 'Klick hier',
    'check-cron-set'                      => 'Überprüfen Sie, wie Cron-Jobs auf Ihrem Server eingerichtet werden.',
    'notification-email'                  => 'E-Mail Benachrichtigungen',
    'click-url-copy'                      => 'Klicken Sie hier, um die URL zu kopieren',
    'job-scheduler-error'                 => 'Job scheduler can not be updated.',
    'job-scheduler-success'               => 'Job scheduler updated successfully.',
    /*
      |--------------------------------------
      |   Ticket Settings Page
      |--------------------------------------
     */
    'ticket'                             => 'Ticket',
    'default_ticket_number_format'       => 'Standard-Ticketnummern Format',
    'default_ticket_number_sequence'     => 'Standard Ticketnummer Sequenz',
    'default_status'                     => 'Standardstatus',
    'default_priority'                   => 'Standardpriorität',
    'default_sla'                        => 'Standard-SLA',
    'default_help_topic'                 => 'Standard-Hilfethema',
    'maximum_open_tickets'               => 'Maximale offene Tickets',
    'agent_collision_avoidance_duration' => 'Agent Kollisionsvermeidungsdauer',
    'human_verification'                 => 'Menschliche Verifizierung',
    'claim_on_response'                  => 'Anspruch auf Antwort',
    'assigned_tickets'                   => 'Zugewiesene Tickets',
    'answered_tickets'                   => 'Beendete Tickets',
    'agent_identity_masking'             => 'Agentenidentitätsmaskierung',
    'enable_HTML_ticket_thread'          => 'HTML-Ticket-Thread aktivieren',
    'allow_client_updates'               => 'Client-Updates zulassen',
    /*
      |--------------------------------------
      |   Access Settings Page
      |--------------------------------------
     */
    'access'                                           => 'Zugriff',
    'expiration_policy'                                => 'Ablaufrichtlinien für Kennwörter',
    'allow_password_resets'                            => 'Passwort zurücksetzen zulassen',
    'reset_token_expiration'                           => 'Token-Ablaufzeit zurücksetzen',
    'agent_session_timeout'                            => 'Zeitlimit für Agentensitzung',
    'bind_agent_session_IP'                            => 'Binde Agentensitzung an IP',
    'registration_required'                            => 'Registrierung benötigt',
    'require_registration_and_login_to_create_tickets' => 'Erfordert die Registrierung und Anmeldung zum Erstellen von Tickets',
    'registration_method'                              => 'Registrierungsmethode',
    'user_session_timeout'                             => 'Zeitlimit für Benutzersitzung',
    'client_quick_access'                              => 'Client-Schnellzugriff',
    'cron'                                             => 'Cron',
    'system-settings'                                  => 'Systemeinstellungen',
    'settings-2'                                       => 'Einstellungen',

    /*
      |--------------------------------------
      |   Auto-Response Settings Page
      |--------------------------------------
     */
    'auto_responce'                 => 'Automatische Antwort',
    'new_ticket'                    => 'Neues Ticket',
    'new_ticket_by_agent'           => 'Neues Ticket vom Agenten',
    'new_message'                   => 'Neue Nachricht',
    'submitter'                     => 'Übermittler: ',
    'send_receipt_confirmation'     => 'Sende Empfangsbestätigung',
    'participants'                  => 'Teilnehmer: ',
    'send_new_activity_notice'      => 'Sende eine neue Aktivitätsbenachrichtigung',
    'overlimit_notice'              => 'Überschreitungshinweis',
    'email_attachments_to_the_user' => 'E-Mail-Anhänge an den Benutzer',
    /*
      |--------------------------------------
      |   Alert & Notice Settings Page
      |--------------------------------------
     */
    'disable'                                               => 'Disable',
    'admin_email_2'                                         => 'Admin Email',
    'alert_notices'                                         => 'Alert & Notices',
    'new_ticket_alert'                                      => 'New Ticket Alert',
    'department_manager'                                    => 'Department Manager',
    'department_members'                                    => 'Department Members',
    'organization_account_manager'                          => 'Organization Account Manager',
    'new_message_alert'                                     => 'New Message Alert',
    'last_respondent'                                       => 'Last Respondent',
    'assigned_agent_team'                                   => 'Assigned Agent / Team',
    'new_internal_note_alert'                               => 'New Internal Note Alert',
    'ticket_assignment_alert'                               => 'Ticket Assignment Alert',
    'team_lead'                                             => 'Team Lead',
    'team_members'                                          => 'Team Members',
    'ticket_transfer_alert'                                 => 'Ticket Transfer Alert',
    'overdue_ticket_alert'                                  => 'Overdue Ticket Alert ',
    'system_alerts'                                         => 'System Alerts',
    'system_errors'                                         => 'System Errors',
    'SQL_errors'                                            => 'SQL errors',
    'excessive_failed_login_attempts'                       => 'Excessive failed login attempts',
    'max_attempt_executed'                                  => 'Zu viele fehlgeschlagene Versuche. Bitte versuchen Sie es in :retry_after Minute(n) erneut.',
    'system_error_reports'                                  => 'System error Reports',
    'Send_app_crash_reports_to_help_Ladybird_improve_Faveo' => 'Send app crash reports to help Ladybird improve Faveo',
    /*
      |------------------------------------------------
      |Language page
      |------------------------------------------------
     */
    'default'            => 'default',
    'iso-code'           => 'ISO-CODE',
    'download'           => 'Downlaod',
    'upload_file'        => 'Upload File',
    'enter_iso-code'     => 'Enter ISO-CODE',
    'eg.'                => 'Example',
    'for'                => 'for',
    'english'            => 'English',
    'language-name'      => 'Language name',
    'file'               => 'File',
    'read-more'          => 'Read more.',
    'enable_lang'        => 'Enable it.',
    'add-lang-package'   => 'Add new language package',
    'package_exist'      => 'Package already exists.',
    'iso-code-error'     => 'Error in iso-code. enter correct iso-code.',
    'zipp-error'         => 'Error in zip file. Zip must contian language php files only.',
    'upload-success'     => 'Uploaded successfully.',
    'file-error'         => 'Error in file or invalid file.',
    'delete-success'     => 'Language package deleted successfully.',
    'lang-doesnot-exist' => 'Language package does not exist.',
    'active-lang-error'  => 'Language package can not be deleted when it is active.',
    'language-error'     => 'Language package not found in your lang directroy.',
    'lang-fallback-lang' => 'Cannot delete system\'s defualt fallback language',
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
    'manage' => 'Manage',
    /*
      |--------------------------------------
      |  Help Topic index Page
      |--------------------------------------
     */
    'help_topics'       => 'Help Topics',
    'topic'             => 'Topic',
    'type'              => 'Type',
    'priority'          => 'Priority',
    'last_updated'      => 'Last Updated',
    'create_help_topic' => 'Create Help topic',
    'action'            => 'Action',
    /*
      |--------------------------------------
      |  Help Topic Create Page
      |--------------------------------------
     */
    'active'               => 'Active',
    'disabled'             => 'Disabled',
    'public'               => 'Public',
    'private'              => 'Private',
    'parent_topic'         => 'Parent Topic',
    'Custom_form'          => 'Custom Form',
    'SLA_plan'             => 'SLA Plan',
    'auto_assign'          => 'Auto assign',
    'auto_respons'         => 'Auto Respons',
    'ticket_number_format' => 'Ticket Number Format',
    'system_default'       => 'System Default',
    'custom'               => 'Custom',
    'internal_notes'       => 'Internal Notes',
    /*
      |--------------------------------------
      |  SLA plan Index Page
      |--------------------------------------
     */
    'sla_plans'    => 'SLA Pläne',
    'create_SLA'   => 'Erstelle ein SLA',
    'grace_period' => 'Schonfrist',
    'added_date'   => 'Erstellungsdatum',
    /*
      |--------------------------------------
      |  SLA plan Create Page
      |--------------------------------------
     */
    'transient'            => 'Transient',
    'ticket_overdue_alert' => 'Ticket Overdue Alerts',

    /*
      |--------------------------------------
      |  Work Flow
      |--------------------------------------
     */
    'workflow'                   => 'Workflow',
    'ticket_workflow'            => 'Ticket Workflow',
    'create_workflow'            => 'Create Workflow',
    'edit_workflow'              => 'Edit Workflow',
    'updated'                    => 'Updated',
    'target'                     => 'Target',
    'target_channel'             => 'Target Channel',
    'exceution_order'            => 'Exceution Order',
    'target_channel'             => 'Target Channel',
    'workflow_rules'             => 'Workflow Rules',
    'workflow_action'            => 'Workflow Action',
    'workflow_rule_incomplete'   => 'Bitte füllen Sie jede Workflow-Regelzeile vollständig aus.',
    'workflow_action_incomplete' => 'Bitte wählen Sie für jede Workflow-Aktionszeile eine Aktion und deren Wert aus.',
    'rules'                      => 'Rules',
    'order'                      => 'Order',
    'condition'                  => 'Condition',
    'statement'                  => 'Statement',

    /*
      |--------------------------------------
      |  Form Create Page
      |--------------------------------------
     */
    'title'                                 => 'Title',
    'instruction'                           => 'Anweisung',
    'label'                                 => 'Beschriftung',
    'visibility'                            => 'Sichtbarkeit',
    'variable'                              => 'Variable',
    'create_form'                           => 'Formular erstellen',
    'forms'                                 => 'Formulare',
    'form_name'                             => 'Formularname',
    'view_this_form'                        => 'Dieses Formular anzeigen',
    'delete_from'                           => 'Formular löschen',
    'are_you_sure_you_want_to_delete'       => 'Sind Sie sicher, dass Sie löschen möchten?',
    'close'                                 => 'Schliessen',
    'instructions'                          => 'Anweisungen',
    'instructions_on_creating_form'         => 'Wählen Sie den Feldtyp aus, den Sie dem untenstehenden Formular hinzufügen möchten, und klicken Sie auf die Dropdown-Liste "Typ". Vergessen Sie nicht, Feldoptionen zu setzen, wenn Typ "Checkbox" oder "Radio" ausgewählt ist... Trennen Sie jede Option durch ein Koma. Nachdem Sie die Erstellung des Formulars abgeschlossen haben, können Sie das Formular speichern, indem Sie auf die Schaltfläche Formular speichern klicken.',
    'form_properties'                       => 'Formulareigenschaften',
    'adding_fields'                         => 'Hinzufügen von Feldern',
    'click_add_fields_button_to_add_fields' => "Klicke <b>'Felder hinzufügen'</b> Schaltfläche zum Hinzufügen von Feldern",
    'add_fields'                            => 'Felder hinzufügen',
    'save_form'                             => 'Formular speichern',
    'name'                                  => 'Name',
    'type'                                  => 'Typ',
    'values(selected_fields)'               => 'Werte(Ausgewählte Felder)',
    'required'                              => 'Erforderlich',
    'Action'                                => 'Aktion',
    'remove'                                => 'E ntfernen',
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
    'themes' => 'Themes',
    /*
      |--------------------------------------
      |  Footer Pages
      |--------------------------------------
     */
    'footer'  => 'Footer',
    'footer1' => 'Footer1',
    'footer2' => 'Footer2',
    'footer3' => 'Footer3',
    'footer4' => 'Footer4',
    /*
      |--------------------------------------
      |  Custom alert box
      |--------------------------------------
     */
    'ok'             => 'Ok',
    'cancel'         => 'Abbrechen',
    'select-ticket'  => 'Bitte Tickets auswählen.',
    'confirm'        => 'Sind Sie sicher?',
    'delete-tickets' => 'Lösche Tickets',
    'close-tickets'  => 'Schließe Tickets',
    'open-tickets'   => 'Öffne Tickets',

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
    'are_you_sure' => 'Bist du sicher',
    'staffs'       => 'Mitarbeiter',
    'name'         => 'Name',
    'user_name'    => 'Nutzername',
    'status'       => 'Status',
    'group'        => 'Gruppe',
    'department'   => 'Abteilung',
    'created'      => 'Erstellt',
    'lastlogin'    => 'Letzter Login',
    'createagent'  => 'Erstelle einen Agenten',
    'delete'       => 'Löschen',
    'agents'       => 'Agenten',
    'create'       => 'Erstellen',
    'edit'         => 'Bearbeiten',
    'departments'  => 'Abteilungen',
    'groups'       => 'Gruppen',
    /*
      |--------------------------------------
      |  Staff Create Page
      |--------------------------------------
     */
    'create_agent'           => 'Agent erstellen',
    'first_name'             => 'Vorname',
    'last_name'              => 'Nachname',
    'mobile_number'          => 'Handynummer',
    'agent_signature'        => 'Agentensignatur',
    'account_status_setting' => 'Kontostatus und Einstellung',
    'account_type'           => 'Kontotyp',
    'admin'                  => 'Administrator',
    'agent'                  => 'Agent',
    'account_status'         => 'Kontostatus',
    'locked'                 => 'verschlossen',
    'assigned_group'         => 'Zugewiesene Gruppe',
    'primary_department'     => 'Primärabteilung',
    'agent_time_zone'        => 'Agentenzeitzone',
    'day_light_saving'       => 'Sommerzeit',
    'limit_access'           => 'Zugriff einschränken',
    'directory_listing'      => 'Verzeichnisliste',
    'vocation_mode'          => 'Bewerbungsmodus',
    'assigned_team'          => 'Zugewiesenes Team',
    /*
      |--------------------------------------
      |  Department Create Page
      |--------------------------------------
     */
    'create_department'                                => 'Abteilung erstellen',
    'manager'                                          => 'Manager',
    'ticket_assignment'                                => 'Ticket-Zuweisung',
    'restrict_ticket_assignment_to_department_members' => 'Beschränken Sie die Ticketzuordnung auf Abteilungsmitglieder',
    'outgoing_emails'                                  => 'Ausgehende E-Mails',
    'outgoing_email'                                   => 'Ausgehende E-Mail',
    'template_set'                                     => 'Vorlagensatz',
    'auto_responding_settings'                         => 'Automatisch reagierende Einstellungen',
    'disable_for_this_department'                      => 'Deaktivieren Sie für diese Abteilung',
    'auto_response_email'                              => 'Automatische Antwort-E-Mail',
    'recipient'                                        => 'Empfänger',
    'group_access'                                     => 'Gruppenzugriff',
    'department_signature'                             => 'Abteilungs Signature',
    /*
      |--------------------------------------
      |  Team Create Page
      |--------------------------------------
     */
    'create_team'           => 'Team erstellen',
    'team_lead'             => 'Teamleiter',
    'assignment_alert'      => 'Zuweisungsalarm',
    'disable_for_this_team' => 'Deaktivieren Sie für dieses Team',
    'teams'                 => 'Teams',
    /*
      |--------------------------------------
      |  Group Create Page
      |--------------------------------------
     */
    'create_group'         => 'Gruppe erstellen',
    'goups'                => 'Gruppe',
    'can_create_ticket'    => 'Kann ein Ticket erstellen',
    'can_edit_ticket'      => 'Kann Ticket bearbeiten',
    'can_post_ticket'      => 'Kann Ticket buchen',
    'can_close_ticket'     => 'Kann Ticket schließen ',
    'can_assign_ticket'    => 'Kann ein Ticket zuweisen',
    'can_transfer_ticket'  => 'Kann Ticket übertragen',
    'can_delete_ticket'    => 'Kann Ticket löschen',
    'can_ban_emails'       => 'Kann E-Mails verbieten',
    'can_manage_premade'   => 'Kann vorgefertigt verwalten',
    'can_manage_FAQ'       => 'Kann FAQ verwalten',
    'can_view_agent_stats' => 'Kann Agentenstatistiken anzeigen',
    'department_access'    => 'Department Access ',
    'admin_notes'          => 'Abteilung Zugriff',
    'group_members'        => 'Gruppenmitglieder',
    'group_name'           => 'Gruppenname',
    /*
      |--------------------------------------
      |  SMTP Page
      |--------------------------------------
     */
    'driver'     => 'Treiber',
    'smtp'       => 'SMTP',
    'host'       => 'Host',
    'port'       => 'Port',
    'encryption' => 'Verschlüsselung',
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
    'agent_panel'       => 'Agenten Panel',
    'profile'           => 'Profil',
    'change_password'   => 'Passwort ändern',
    'sign_out'          => 'Ausloggen',
    'Tickets'           => 'TICKETS',
    'inbox'             => 'Posteingang',
    'my_tickets'        => 'Meine Tickets',
    'unassigned'        => 'Nicht zugewiesen',
    'trash'             => 'Müll',
    'Updates'           => 'UPDATES',
    'no_new_updates'    => 'Keine neuen Updates',
    'check_for_updates' => 'Auf Updates prüfen',
    'open'              => 'öffnen',
    'inprogress'        => 'In Bearbeitung',
    'closed'            => 'Geschlossen',
    'Departments'       => 'ABTEILUNGEN',
    'tools'             => 'Tools',
    'canned'            => 'Vorformulierte',
    'knowledge_base'    => 'Wissensdatenbank',
    'loading'           => 'Wird geladen',
    'ratings'           => 'Bewertungen',
    'please_rate'       => 'Bitte bewerten:',
    'ticket_ratings'    => 'TICKET-BEWERTUNG',
    /*
      |-----------------------------------------------
      |  Profile
      |-----------------------------------------------
     */
    'user_information'    => 'Benutzer Informationen',
    'time_zone'           => 'Zeitzone',
    'phone_number'        => 'Telefonnummer',
    'contact_information' => 'Kontakt Informationen',
    /*
      |-----------------------------------------------
      |  Dashboard
      |-----------------------------------------------
     */
    'dashboard'  => 'Dashboard',
    'line_chart' => 'Liniendiagramm',
    'statistics' => 'Statistiken',
    'opened'     => 'geöffnet',
    'resolved'   => 'gelöste',
    'closed'     => 'geschlossen',
    'deleted'    => 'gelöscht',
    /*
      |------------------------------------------------
      |User Page
      |------------------------------------------------
     */
    'user_directory'  => 'Benutzerverzeichnis',
    'ban'             => 'Ban',
    'user'            => 'Nutzername',
    'users'           => 'Benutzer',
    'create_user'     => 'Benutzer erstellen',
    'full_name'       => 'Vollständiger Name',
    'mobile'          => 'Handynummer',
    'last_login'      => 'Letzter Login',
    'user_profile'    => 'Benutzerprofil',
    'assign'          => 'zuordnen',
    'open_tickets'    => 'Tickets öffnen',
    'closed_tickets'  => 'Geschlossene Tickets',
    'deleted_tickets' => 'Gelöschte Tickets',
    /*
      |------------------------------------------------
      |Organization Page
      |------------------------------------------------
     */
    'organizations'                 => 'Organisationen',
    'organization'                  => 'Organisation',
    'create_organization'           => 'Organisation erstellen',
    'account_manager'               => 'Konto-Manager',
    'update'                        => 'aktualisieren',
    'please_select_an_organization' => 'Bitte wählen Sie eine Organisation',
    'please_select_an_user'         => 'Bitte wählen Sie einen Benutzer aus',
    'organization_profile'          => 'Organisationsprofil',
    'organization-s_head'           => 'Leiter der Organisation',
    'select_department_manager'     => 'Wählen Sie den Abteilungsmanager',
    'users_of'                      => 'Benutzer von',
    /*
      |----------------------------------------------
      |  Ticket page
      |----------------------------------------------
     */
    'subject'                                        => 'Thema',
    'ticket_id'                                      => 'Ticket ID',
    'priority'                                       => 'Priorität',
    'from'                                           => 'Von',
    'last_replier'                                   => 'Letzter Berichterstatter',
    'assigned_to'                                    => 'Zugewiesen an',
    'last_activity'                                  => 'Letzte Aktivität',
    'answered'                                       => 'Beantwortet',
    'assigned'                                       => 'zugewiesen',
    'create_ticket'                                  => 'Ticket erstellen',
    'tickets'                                        => 'Tickets',
    'open'                                           => 'öffnen',
    'Ticket_Information'                             => 'TICKET INFORMATION',
    'Ticket_Id'                                      => 'TICKET ID',
    'User'                                           => 'Benutzer',
    'Unassigned'                                     => 'UNBEAUFSICHTIGT',
    'generate_pdf'                                   => 'PDF erstellen',
    'change_status'                                  => 'Status ändern',
    'more'                                           => 'Mehr',
    'delete_ticket'                                  => 'Ticket löschen',
    'emergency'                                      => 'Notfall',
    'high'                                           => 'Hoch',
    'medium'                                         => 'Mittel',
    'low'                                            => 'Niedrig',
    'sla_plan'                                       => 'SLA Plan',
    'created_date'                                   => 'Erstellungsdatum',
    'due_date'                                       => 'Fälligkeitsdatum',
    'last_response'                                  => 'Letzte Antwort',
    'source'                                         => 'Quelle',
    'last_message'                                   => 'Letzte Nachricht',
    'reply'                                          => 'Antworten',
    'response'                                       => 'Antwort',
    'reply_content'                                  => 'Inhalt antworten',
    'attachment'                                     => 'Anhang',
    'internal_note'                                  => 'Interne Notiz',
    'this_ticket_is_under_banned_user'               => 'Dieses Ticket befindet sich unter einem gesperrten Benutzer',
    'ticket_source'                                  => 'Ticketquelle',
    'are_you_sure_to_ban'                            => 'Bist du sicher, zu verbieten?',
    'whome_do_you_want_to_assign_ticket'             => 'Wen möchten Sie Ticket zuweisen?',
    'are_you_sure_you_want_to_surrender_this_ticket' => 'Möchten Sie dieses Ticket wirklich abgeben?t',
    'add_collaborator'                               => 'Mitarbeiter hinzufügen',
    'search_existing_users'                          => 'Suchen Sie nach vorhandenen Benutzern',
    'add_new_user'                                   => 'Neuen Benutzer hinzufügen',
    'search_existing_users_or_add_new_users'         => 'Suchen Sie nach vorhandenen Benutzern oder fügen Sie neue Benutzer hinzu',
    'search_by_email'                                => 'Suche per E-Mail',
    'list_of_collaborators_of_this_ticket'           => 'Liste der Mitarbeiter dieses Tickets',
    'submit'                                         => 'Einreichen',
    'max'                                            => 'Max',
    'add_cc'                                         => 'Fügen Sie CC hinzu',
    'recepients'                                     => 'Empfänger',
    'select_a_canned_response'                       => 'Wählen Sie eine gespeicherte Antwort aus',
    'assign_to'                                      => 'Zuweisen',
    'detail'                                         => 'Detail',
    'user_details'                                   => 'Nutzerdetails',
    'ticket_option'                                  => 'Ticket Option',
    'ticket_detail'                                  => 'Ticket Detail',
    'Assigned_To'                                    => 'Zugewiesen an',
    'locked-ticket'                                  => 'Aufmerksam! Dieses Ticket wurde von einem anderen Benutzer gesperrt und wird gerade bearbeitet.',
    'access-ticket'                                  => 'Aufmerksam! Dieses Ticket wurde von Ihnen als nächstes gesperrt',
    'minutes'                                        => 'Protokoll',
    'in_minutes'                                     => 'In Minuten',
    'add_another_owner'                              => 'Fügen Sie einen anderen Besitzer hinzu',
    'user-not-found'                                 => 'Benutzer nicht gefunden. Versuchen Sie es erneut oder fügen Sie einen neuen Benutzer hinzu.',
    'change-success'                                 => 'Erfolg! Inhaber wurde für dieses Ticket geändert.',
    'user-exists'                                    => 'Benutzer existiert bereits. Versuchen Sie, den vorhandenen Benutzer zu suchen.',
    'valid-email'                                    => 'Geben sie eine gültige E-Mail-Adresse an.',
    'search_user'                                    => 'Benutzer suchen',
    'merge-ticket'                                   => 'Ticket zusammenlegen',
    'title'                                          => 'Title',
    'merge'                                          => 'Zusammenlegen',
    'select_tickets'                                 => 'Wählen Sie Tickets zum Zusammenführen aus',
    'select-pparent-ticket'                          => 'Wählen Sie das Elternticket aus',
    'merge-reason'                                   => 'Grund für die Zusammenführung',
    'get_merge_message'                              => 'Dieses Ticket wurde mit Ticket zusammengeführt',
    'ticket_merged'                                  => ' wurde mit diesem Ticket zusammengeführt.',
    'no-tickets-to-merge'                            => 'Es gibt keine Tickets mehr vom Besitzer dieses Tickets.',
    'merge-error'                                    => 'Deine Anfrage konnte nach einiger Zeit nicht bearbeitet werden.',
    'merge-success'                                  => 'Tickets wurden erfolgreich zusammengeführt.',
    'merge-error2'                                   => 'Bitte wählen Sie ein Ticket zum Zusammenführen aus.',
    'select-tickets-to merge'                        => 'Wählen Sie zwei oder mehr Tickets zum Zusammenführen aus.',
    'different-users'                                => 'Tickets von verschiedenen Benutzern',
    'clean-up'                                       => 'Aufräumen',
    'hard-delete-success-message'                    => 'Tickets wurden endgültig gelöscht.',
    'overdue'                                        => 'Überfällig',
    'change_owner_for_ticket'                        => 'Besitzer für Ticket ändern',

    /*
      |------------------------------------------------
      |Tools Page
      |------------------------------------------------
     */
    'canned_response'        => 'vorgefertigte Antworten',
    'create_canned_response' => 'Erstellen Sie eine vorgefertigte Antwort',
    'surrender'              => 'Kapitulation',
    'view'                   => 'Aussicht',
    /*
      |-----------------------------------------------
      | Main text
      |-----------------------------------------------
     */
    'copyright'           => 'Copyright',
    'all_rights_reserved' => 'Alle Rechte vorbehalten',
    'powered_by'          => 'Powered by',
    'version'             => 'Version',
    /*
      |------------------------------------------------
      |Guest-User Page
      |------------------------------------------------
     */
    'issue_summary'        => 'Problemzusammenfassung',
    'issue_details'        => 'Details zum Problem',
    'contact_informations' => 'Kontakt Informationen',
    'contact_details'      => 'Kontakt details',
    'role'                 => 'Rolle',
    'ext'                  => 'EXT',
    'profile_pic'          => 'Profilbild',
    'agent_sign'           => 'Agentensignatur',
    'inactive'             => 'Inaktiv',
    'male'                 => 'Männlich',
    'female'               => 'Weiblich',
    'old_password'         => 'Altes Passwort',
    'new_password'         => 'Neues Kennwort',
    'confirm_password'     => 'Bestätige das Passwort',
    'gender'               => 'Geschlecht',
    'ticket_number'        => 'Ticketnummer',
    'content'              => 'Inhalt',
    /*
      |------------------------------------------------
      |   Error Pages
      |------------------------------------------------
     */
    'not_found'                                       => 'Nicht gefunden',
    'oops_page_not_found'                             => 'Hoppla! Seite nicht gefunden',
    'we_could_not_find_the_page_you_were_looking_for' => 'Wir konnten die von Ihnen gesuchte Seite nicht finden',
    'internal_server_error'                           => 'Interner Serverfehler',
    'be_right_back'                                   => 'Ich komme gleich wieder',
    'sorry'                                           => 'Sorry',
    'we_are_working_on_it'                            => 'Wir arbeiten daran',
    'category'                                        => 'Kategorie',
    'addcategory'                                     => 'Kategorie hinzufügen',
    'allcategory'                                     => 'Alle Kategorien',
    'article'                                         => 'Artikel',
    'articles'                                        => 'Artikel',
    'addarticle'                                      => 'Artikel hinzufügen',
    'allarticle'                                      => 'Alle Artikel',
    'pages'                                           => 'Seiten',
    'addpages'                                        => 'Seiten hinzufügen',
    'allpages'                                        => 'Alle Seiten',
    'widgets'                                         => 'Widgets',
    'footer1'                                         => 'Footer 1',
    'footer2'                                         => 'Footer 2',
    'footer3'                                         => 'Footer 3',
    'footer4'                                         => 'Footer 4',
    'sidewidget1'                                     => 'Side Widget 1',
    'sidewidget2'                                     => 'Side Widget 2',
    'comments'                                        => 'Bemerkungen',
    'settings'                                        => 'Einstellungen',
    'parent'                                          => 'Parent',
    'Beschreibung'                                    => 'Beschreibung',
    'enter_the_description'                           => 'Geben Sie die Beschreibung ein',
    'publish'                                         => 'Veröffentlichen',
    'published'                                       => 'Veröffentlicht',
    'draft'                                           => 'Entwurf',
    'create_a_category'                               => 'Erstellen Sie eine Kategorie',
    'add'                                             => 'Hinzufügen',
    'social'                                          => 'Social',
    'comment'                                         => 'Kommentar',
    'not_published'                                   => 'Nicht veröffentlicht',
    'numberofelementstodisplay'                       => 'Anzahl der anzuzeigenden Elemente',
    //======================================
    'language'                                                                 => 'Sprache',
    'save'                                                                     => 'Speichern',
    'create'                                                                   => 'Erstellen',
    'dateformat'                                                               => 'Datumsformat',
    'slug'                                                                     => 'Slug',
    'read_more'                                                                => 'Weiterlesen',
    'view_all'                                                                 => 'Alle ansehen',
    'categories'                                                               => 'Kategorien',
    'need_more_support'                                                        => 'Brauche mehr Unterstützung',
    'if_you_did_not_find_an_answer_please_raise_a_ticket_describing_the_issue' => 'Wenn Sie keine Antwort gefunden haben, erstellen Sie bitte ein Ticket, das das Problem beschreibt',
    'have_a_question?_type_your_search_term_here'                              => 'Have a question? Geben Sie Ihren Suchbegriff hier ein ...',
    'search'                                                                   => 'Suche',
    'frequently_asked_questions'                                               => 'Häufig gestellte Fragen',
    'leave_a_reply'                                                            => 'Hinterlasse eine Antwort',
    'post_message'                                                             => 'Nachrichten posten',
    /*
      |--------------------------------------------------------------------------------------
      |  Client Panel [English(en)]
      |--------------------------------------------------------------------------------------
      | The following language lines are used in all Agent Panel related issues to translate
      | some words in view to English. You are free to change them to anything you want to
      | customize your views to better match your application.
      |
     */
    'article_list'        => 'Artikel liste',
    'category'            => 'Kategorie',
    'home'                => 'Home',
    'submit_a_ticket'     => 'Ein Ticket erstellen',
    'my_profile'          => 'Mein Profil',
    'log_out'             => 'Ausloggen',
    'forgot_password'     => 'Passwort vergessen',
    'create_account'      => 'Benutzerkonto anlegen',
    'you_are_here'        => 'Du bist hier',
    'have_a_ticket'       => 'Habe ein Ticket',
    'check_ticket_status' => 'Prüfe den Ticket Status',
    'choose_a_help_topic' => 'Wählen Sie ein Hilfethema',
    'ticket_status'       => 'Ticketstatus',
    'post_comment'        => 'Kommentar hinzufügen',
    'plugin'              => 'Plugin',

    /***************************************************
     *updates
     *translation required from here
     *****************************************************/
    'edit_profile'                                                                     => 'Profil bearbeiten',
    'Send'                                                                             => 'senden',
    'no_article'                                                                       => 'Kein Artikel',
    'profile_settings'                                                                 => 'Profileinstellungen',
    'please_fill_all_required_feilds'                                                  => 'Bitte alle notwendigen Felder ausfüllen.',
    'successfully_replied'                                                             => 'Erfolgreich geantwortet',
    'please_fill_some_data'                                                            => 'Bitte füllen Sie einige Daten aus!',
    'profile_updated_sucessfully'                                                      => 'Profil erfolgreich aktualisiert',
    'password_updated_sucessfully'                                                     => 'Passwort wurde erfolgreich aktualisiert',
    'password_was_not_updated_incorrect_old_password'                                  => 'Das Passwort wurde nicht aktualisiert. Falsches altes Passwort',
    'there_is_no_such_ticket_number'                                                   => 'Es gibt keine solche Ticketnummer',
    "email_didn't_match_with_ticket_number"                                            => 'Die E-Mail-Adresse stimmt nicht mit der Ticketnummer überein',
    'we_have_sent_you_a_link_by_email_please_click_on_that_link_to_view_ticket'        => 'Wir haben Ihnen einen Link per E-Mail gesendet. Bitte klicken Sie auf diesen Link, um das Ticket anzuzeigen',
    'no_records_on_publish_time'                                                       => 'Keine Einträge zur Veröffentlichungszeit',
    'your_details_send_to_system'                                                      => 'Ihre Daten werden an das System gesendet',
    'your_details_can_not_send_to_system'                                              => 'Ihre Daten können nicht an das System gesendet werden',
    'your_comment_posted'                                                              => 'Dein Kommentar wurde gepostet',
    'sorry_not_processed'                                                              => 'Entschuldigung nicht bearbeitet',
    'profile_updated_sucessfully'                                                      => 'Profil erfolgreich aktualisiert',
    'password_was_not_updated'                                                         => 'Das Passwort wurde nicht aktualisiert',
    'sorry_your_ticket_token_has_expired_please_try_to_resend_the_ticket_link_request' => 'Entschuldigung, dass Ihr Ticket-Token abgelaufen ist! Bitte versuchen Sie erneut, die Ticket-Link-Anfrage zu senden',
    'sorry_you_are_not_allowed_token_expired'                                          => 'Entschuldigung, Sie sind nicht erlaubt. Token ist abgelaufen!',
    'thank_you_for_your_rating'                                                        => 'Danke für deine Bewertung!',
    'your_ticket_has_been'                                                             => 'Dein Ticket wurde',
    'failed_to_send_email_contact_administrator'                                       => 'E-Mail-Versand fehlgeschlagen. Bitte kontaktieren Sie den Systemadministrator',
    /*
     * |---------------------------------------------------------------------------------------
     * |API settings
     * |----------------------------------------------------------------------------------
     * |The following lanuage line used to get english traslation of api settings in admin panel
     * |
     * |
     */
    'webhooks'                         => 'Webhooks',
    'enter_url_to_send_ticket_details' => 'Geben Sie die URL ein, um die Ticketdetails zu senden',
    'api'                              => 'API',
    'api_key'                          => 'API Schlüssel',
    'api_key_mandatory'                => 'API key mandatory',
    'api_configurations'               => 'API configurations',
    'generate_key'                     => 'Generiere Schlüssel',
    'api_settings'                     => 'API Einstellungen',
    /*
     * -----------------------------------------------------------------------------
     * Error log and debugging settings
     * --------------------------------------------------------------------------
     *
     */
    'error-debug'                        => 'Fehlerprotokolle und Debugging',
    'debug-options'                      => 'Debugging-Optionen',
    'view-logs'                          => 'Fehlerprotokolle anzeigen',
    'not-authorised-error-debug'         => 'Sie sind nicht berechtigt, auf die URL zuzugreifen',
    'error-debug-settings'               => 'Error and debugging settings',
    'debugging'                          => 'Debugging-Modus',
    'bugsnag-debugging'                  => 'Sende App-Absturzberichte, um Ladybird dabei zu helfen, Faveo zu verbessern',
    'error-debug-settings-saved-message' => 'Ihre Fehler- und Debugging-Einstellungen wurden erfolgreich gespeichert',
    'error-debug-settings-error-message' => 'Sie haben die Einstellungen nicht geändert.',
    'error-logs'                         => 'Fehlerprotokolle',
    /* ---------------------------------------------------------------------------------------
     * Latest update 16-06-2016
     * -----------------------------------------------------------------------------------
     */
    'that_email_is not_available_in_this_system' => 'Diese E-Mail-Adresse ist in diesem System nicht verfügbar',
    'use_subject'                                => 'Verwenden Sie Betreff',
    'reopen'                                     => 'Wieder geöffnet',
    'invalid_attempt'                            => 'Ungültiger Versuch',
    /* ---------------------------------------------------------------------------------------
     * Latest update 27-07-2016
     * -----------------------------------------------------------------------------------
     */
    'queue'  => 'Warteschlange',
    'queues' => 'Warteschlangen',
    /*     * -------------------------------------------------------------------------------------------------
     * OTP  messages body to send to user while registering, resetting passwords
     * --------------------------------------------------------------------------------------------------
     */
    'hello'                   => 'Hallo',
    'reset-link-msg'          => ",\r\nHier ist der Link zum Zurücksetzen Ihres Passworts.\r\n",
    'otp-for-your'            => ",\r\nOTP für Ihre",
    'account-verification-is' => 'Kontobestätigung ist',
    'extra-text'              => ".\r\nSie können sich anmelden, um Ihr Konto über OTP zu bestätigen, oder klicken Sie einfach auf den Link, den wir an Ihre E-Mail-Adresse gesendet haben.",
    'otp-not-sent'            => 'Wir hatten Probleme beim Senden von OTP, bitte versuchen Sie es nach einiger Zeit.',
    /*     * -------------------------------------------------------------------------------------------
     * Ticket number settings 03-08-2016
     * ------------------------------------------------------------------------------------------
     */
    'format'               => 'Format',
    'ticket-number-format' => 'Diese Einstellung wird verwendet, um Ticketnummern zu generieren. Verwenden Sie Hash-Zeichen (`#`) wo Ziffern platziert werden sollen & Dollarzeichen(`$`) wo Zeichen platziert werden sollen. Jeder andere Text im Zahlenformat bleibt erhalten. ',
    'ticket-number-type'   => 'Wählen Sie eine Sequenz, aus der neue Ticketnummern abgeleitet werden sollen. Das System hat standardmäßig eine Inkrementierungssequenz und eine Zufallssequenz',
    /*     * ----------------------------------------------------------------------------------------------------
     * Social media integration
     * ---------------------------------------------------------------------------------------------------------
     */
    'client_id'     => 'Client id',
    'client_secret' => 'Client secret',
    'redirect'      => 'Redirect URL',
    'details'       => 'Details',
    'social-media'  => 'Social media',
    /*     * ----------------------------------------------------------------------------------------------
     * Report
     * ----------------------------------------------------------------------------------------------
     */
    'report'              => 'Report',
    'Report'              => 'REPORT',
    'start_date'          => 'Anfangsdatum',
    'end_date'            => 'Enddatum',
    'select'              => 'Wählen',
    'generate'            => 'Generieren',
    'day'                 => 'Tag',
    'week'                => 'Woche',
    'month'               => 'Monat',
    'Currnet_In_Progress' => 'Aktuell in bearbeitung',
    'Total_Created'       => 'GESAMT ERSTELLT',
    'Total_Reopened'      => 'GESAMT WIEDERÖFFNET',
    'Total_Closed'        => 'GESAMT GESCHLOSSEN',
    'tabular'             => 'Tabellarisch',
    'reopened'            => 'Wieder geöffnet',
    /* ---------------------------------------------------------------------------------------
     * Ticket Priority
     * -----------------------------------------------------------------------------------
     */
    'ticket_priority'                                           => 'Ticketpriorität',
    'priority'                                                  => 'Priorität',
    'priority_desc'                                             => 'Prioritätsbeschreibung',
    'priority_urgency'                                          => 'Priorität der Priorität',
    'priority_id'                                               => 'Prioritätskennung',
    'priority_color'                                            => 'Prioritätsfarbe',
    'ispublic'                                                  => 'Ist Öffentlich',
    'is_default'                                                => 'Standardmäßig',
    'create_ticket_priority'                                    => 'Ticketpriorität erstellen',
    'agent_notes'                                               => 'Agenten Notizen',
    'select_priority'                                           => 'Wählen Sie die Priorität aus',
    'normal'                                                    => 'Normal',
    'ispublic'                                                  => 'Sichtweite',
    'make-default-priority'                                     => 'Mache Standardpriorität',
    'priority_successfully_created'                             => 'Die Priorität wurde erfolgreich erstellt',
    'priority_successfully_updated'                             => 'Priorität erfolgreich aktualisiert',
    'delete_successfully'                                       => 'Löschen erfolgreich',
    'user_priority_status'                                      => 'Benutzerprioritätsstatus',
    'current'                                                   => 'Aktuell:',
    'active_user_can_select_the_priority_while_creating_ticket' => 'Aktiver Benutzer kann beim Erstellen eines Tickets die Priorität auswählen',

    /* --------------------------------------------------------------------------------------------
     * Approval Updated
     * --------------------------------------------------------------------------------------------
     */
    'approval'             => 'Genehmigung',
    'approval_tickets'     => 'Genehmigungstickets',
    'approve'              => 'Approve',
    'approval_request'     => 'Genehmigen',
    'approvel_ticket_list' => 'Ticketliste genehmigen',

    'approval_settings'                      => 'Genehmigungseinstellungen',
    'close_all_ticket_for_approval'          => 'Schließen Sie alle Tickets zur Genehmigung',
    'approval_settings-created-successfully' => 'Genehmigungseinstellungen erfolgreich erstellt',

    /* --------------------------------------------------------------------------------------------
     * Followup Updated
     * --------------------------------------------------------------------------------------------
     */
    'followup'              => 'Nachverfolgen',
    'followup_tickets'      => 'Followup tickets',
    'followup_Notification' => 'Follow-up-Benachrichtigung',

    /*
      *--------------------------------------------------------------------------------------------
      *Updated 6-9-2016
      *---------------------------------------------------------------------------------------
      */
    'not-available' => 'Nicht verfügbar',
    /* --------------------------------------------------------------------------------------------
     * User Module
     * --------------------------------------------------------------------------------------------
     */
    'agent_report'                                                 => 'Agentenbericht',
    'assign_tickets'                                               => 'Tickets zuweisen',
    'delete_agent'                                                 => 'Löschen Sie den Agenten',
    'delete_user'                                                  => 'Benutzer löschen',
    'confirm_deletion'                                             => 'Löschung bestätigen',
    'delete_all_content'                                           => 'Lösche den gesamten Inhalt',
    'agent_profile'                                                => 'Agentenprofil',
    'change_role_to_admin'                                         => 'Ändern Sie die Rolle in Admin',
    'change_role_to_user'                                          => 'Ändern Sie die Rolle in Benutzer',
    'change_role_to_agent'                                         => 'Ändern Sie die Rolle in Agent',
    'change_password'                                              => 'Passwort ändern',
    'role_change'                                                  => 'Rollenwechsel',
    'password_generator'                                           => 'Passwortgenerator',
    'depertment'                                                   => 'Abteilung',
    'duetoday'                                                     => 'Heute fällig',
    'today-due_tickets'                                            => 'Heutige Tickets',
    'password_change_successfully'                                 => 'Passwort erfolgreich geändert',
    'role_change_successfully'                                     => 'Die Rolle wurde erfolgreich geändert',
    'user_delete_successfully'                                     => 'Benutzer wurde erfolgreich gelöscht',
    'agent_delete_successfully'                                    => 'Agent wurde erfolgreich gelöscht',
    'select_another_agent'                                         => 'Wählen Sie einen anderen Agenten aus',
    'agent_delete_successfully_and_ticket_assign_to_another_agent' => 'Der Agent wurde erfolgreich gelöscht und das Ticket wurde einem anderen Agenten zugewiesen',
    'deleted_user'                                                 => 'Gelöschter Benutzer',
    'deleted_user_directory'                                       => 'Gelöschtes Benutzerverzeichnis',
    'restore'                                                      => 'Wiederherstellen',
    'user_restore_successfully'                                    => 'Benutzerwiederherstellung erfolgreich',

    /*** updates 28-11-2016***/
    'apply' => 'anwenden',

    /* updates 2-12-2016 **/
    'sort-by'                      => 'Sortiere nach',
    'created-at'                   => 'Erstellt am',
    'or'                           => 'oder',
    'activate'                     => 'Aktivieren',
    'system-email-not-configured'  => 'Wir können die E-Mail-Anfrage nicht verarbeiten, da das System keine konfigurierte E-Mail zum Senden von E-Mails hat. Bitte kontaktieren Sie den Systemadministrator und benachrichtigen Sie ihn.',
    'assign-ticket'                => 'Tickets zuweisen',
    'can-not-inactive-group'       => 'Die Gruppe kann nicht inaktiviert werden, da ihr Agenten zugewiesen sind. Weisen Sie diese Agenten einer anderen Gruppe zu und versuchen Sie es erneut.',
    'internal-note-has-been-added' => 'Interne Notiz wurde dem Ticket hinzugefügt',
    'active-users'                 => 'Aktive Benutzer',
    'deleted-users'                => 'Gelöschte Benutzer',
    'view-option'                  => 'Optionen anzeigen',
    'accoutn-not-verified'         => 'Benutzerkonto wurde nicht überprüft',
    'enabled'                      => 'Aktiviert',
    'disabled'                     => 'Deaktiviert',
    'user-account-is-deleted'      => 'Dieses Benutzerkonto wurde gelöscht.',
    'restore-user'                 => 'Benutzerkonto wiederherstellen',
    'delete-account-caution-info'  => 'Bitte beachten Sie, dass dieses Konto möglicherweise noch offene Tickets im System hat.',
    'reply-can-not-be-empty'       => 'Antwort kann nicht leer sein. Bitte geben Sie Ihre Antwort ein.',

    //update 18-12-2016
    'account-created-contact-admin-as-we-were-not-able-to-send-opt' => 'Ihr Konto wurde erfolgreich erstellt. Bitte kontaktieren Sie den Administrator für die Kontoaktivierung, da wir Ihnen keinen OPT-Code senden konnten.',
    //update 19-12-2016
    'only-agents'    => 'Agentenbenutzer',
    'only-users'     => 'Kunden Benutzer',
    'banned-users'   => 'Gesperrte Benutzer',
    'inactive-users' => 'Inaktiver Benutzer',
    'all-users'      => 'Alle Nutzer',
    //update 21-12-2016
    'selected-user-is-already-the-owner' => 'Der ausgewählte Benutzer ist bereits Inhaber dieses Tickets.',
    //updated 15-5-2017
    'session-expired' => 'Die Sitzung ist abgelaufen oder ungültig. Bitte versuchen Sie es erneut.',
    //updated 10-10-2023
    'what_should_be_done_with_content_owned_by_this_user'=> 'Was soll mit Inhalten geschehen, die diesem Benutzer gehören',
];
