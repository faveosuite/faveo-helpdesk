<?php

/*
 |-------------------------------------------------------------------------------------
 |      Dutch translation [for version 1.0.8.0]
 |-------------------------------------------------------------------------------------
 |      Author      : Rob Traanman
 |      Email       : Rob@systemedic.nl
 |  Last translated : 30-11-2016
 |********************************************************************************
 |      Details of new words added for translation
 |--------------------------------------------------------------------------------
 |        Added by  :
 |     translated   : [NO/YES]
 |      Added on    :
 */

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
    'success'                                                        => 'Succes',
    'fails'                                                          => 'Fout',
    'alert'                                                          => 'Opgelet',
    'warning'                                                        => 'Waarschuwing',
    'required-error'                                                 => 'Vul alle vereiste velden in',
    'invalid'                                                        => 'Onjuiste gebruikers ID of wachtwoord',
    'sorry_something_went_wrong'                                     => 'Sorry, er is iets mis gegaan',
    'were_working_on_it_and_well_get_it_fixed_as_soon_as_we_can'     => 'We doen ons best om het zo snel mogelijk te herstellen',
    'we_are_sorry_but_the_page_you_are_looking_for_can_not_be_found' => 'Sorry, we kunnen de pagina die je zoekt niet vinden',
    'go_back'                                                        => 'Ga terug',
    'the_board_is_offline'                                           => 'Het systeem is offline',
    'error_establishing_connection_to_database'                      => 'Kan geen toegang krijgen tot de database',
    'unauthorized_access'                                            => 'Onbevoegde toegang',
    'not-autherised'                                                 => 'Je hebt geen toegang',
    'otp-not-matched'                                                => 'Oops! De OTP code welke je hebt ingevoerd komt niet overeen met de code welke we je hebben gestuurd.',
    'otp-invalid'                                                    => 'OTP code moet een 6-cijferig nummer zijn.',
    'otp-input-title'                                                => 'Voer de 6 cijfers van de OTP code in.',
    'otp-expired'                                                    => 'Je OTP code is vervallen.<br/> Klik op "OTP Code Opnieuw sturen" om een nieuwe OTP code te ontvangen.',
    'resend-otp-title'                                               => 'Klik hier om de OTP code opnieuw te laten versturen',
    /*
      |--------------------------------------
      |   Login Page
      |--------------------------------------
     */
    'login_to_start_your_session'        => 'Log in om te beginnen',
    'login'                              => 'Inloggen',
    'remember'                           => 'Onthoud mij',
    'signmein'                           => 'Inloggen',
    'iforgot'                            => 'Ik ben mijn wachtwoord vergeten',
    'email_address'                      => 'E-mailadres',
    'password'                           => 'Wachtwoord',
    'woops'                              => 'Oeps!!',
    'theirisproblem'                     => 'Er is een probleem met je invoer.',
    'e-mail'                             => 'E-mailadres',
    'reg_new_member'                     => 'Registreren',
    'this_account_is_currently_inactive' => 'Dit account is niet actief!',
    'not-registered'                     => 'E-mailadres / gebruikersnaam onbekend',
    'verify'                             => 'Controleren',
    'enter-otp'                          => 'OTP invoeren',
    'did-not-recive-code'                => 'Code niet ontvangen?',
    'resend_otp'                         => 'OTP opnieuw versturen',
    'verify-screen-msg'                  => 'Je account is niet geactiveerd.<br/>Voer je OTP in, welke we per e-mail hebben verstuurd, om je account te activeren',
    'otp-sent'                           => 'We hebben een OTP code naar je telefoonnummer gestuurd.',
    'verify-number'                      => 'Nummer controleren',
    'get-verify-message'                 => 'Voer de OTP code in welke we naar je nieuwe nummer hebben gestuurd.',
    'number-verification-sussessfull'    => 'Je nummer is succesvol gecontroleerd. Even geduld; we werken je profiel bij..',
    /*
      |--------------------------------------
      |   Register Page
      |--------------------------------------
     */
    'registration'                                                                                => 'Registratie',
    'full_name'                                                                                   => 'Volledige naam',
    'firstname'                                                                                   => 'Voornaam',
    'lastname'                                                                                    => 'Achternaam',
    'profilepicture'                                                                              => 'Profielfoto',
    'oldpassword'                                                                                 => 'Oude wachtwoord',
    'newpassword'                                                                                 => 'Nieuwe wachtwoord',
    'retype_password'                                                                             => 'Herhaal wachtwoord',
    'i_agree_to_the'                                                                              => 'Ik ga akkoord met de',
    'terms'                                                                                       => 'algemene voorwaarden',
    'register'                                                                                    => 'Registreren',
    'i_already_have_a_membership'                                                                 => 'Ik heb al een profiel',
    'see-profile1'                                                                                => 'Klik hier om dit te bekijken ',
    'see-profile2'                                                                                => 's profiel',
    'activate_your_account_click_on_Link_that_send_to_your_mail'                                  => 'Activeer je account ! Klik op de link welke we naar je e-mailadres hebben gestuurd',
    'activate_your_account_click_on_Link_that_send_to_your_mail_and_moble'                        => 'Activeer je account ! Klik op de link welke we naar je e-mailadres hebben gestuur of login op je account en voer de OTP code in welke we naar je mobiele nummer hebben gestuurd.',
    'activate_your_account_click_on_Link_that_send_to_your_mail_sms_plugin_inactive_or_not_setup' => 'Account aangemaakt. Neem contact op met de systeembeheer want we konden geen OTP code naar je mobiele nummer verzenden, ook konden we geen e-mail versturen naar je e-mailadres.',
    'this_field_do_not_match_our_records'                                                         => 'Dit veld komt niet overeen met onze gegevens.',
    'we_have_e-mailed_your_password_reset_link'                                                   => 'We hebben een link verstuurd om je wachtwoord te resetten!',
    "we_can't_find_a_user_with_that_e-mail_address"                                               => 'We kunnen geen gebruiker met dat e-mailadres vinden.',
    /*
      |--------------------------------------
      |   Reset Password Page
      |--------------------------------------
     */
    'reset_password'              => 'Wachtwoord resetten',
    'password-reset-successfully' => 'Je wachtwoord is gereset. Login op je account met je nieuwe wachtwoord.',
    'password-can-not-reset'      => 'We konden je wachtwoord niet resetten. Probeer het later nog eens.',
    /*
      |--------------------------------------
      |   Forgot Password Page
      |--------------------------------------
     */
    'i_know_my_password'            => 'Ik weet mijn wachtwoord',
    'recover_passord'               => 'Wachtwoord resetten',
    'send_password_reset_link'      => 'Stuur wachtwoord reset link',
    'enter_email_to_reset_password' => 'Voer een e-mailadres of mobiel nummer in om je wachtwoord te resetten',
    'link'                          => 'Link',
    'email_or_mobile'               => 'E-mailadres of mobiele telefoonnummer',
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
    'admin_panel' => 'Beheerpaneel',
    /*
      |--------------------------------------
      |  Emails Create Page
      |--------------------------------------
     */
    'emails'                                                                           => 'E-mailberichten',
    'incoming_emails'                                                                  => 'Binnenkomende e-mailberichten',
    'reuired_authentication'                                                           => 'Vereist authenticatie',
    'fetching_email_via_imap'                                                          => 'E-mail via IMAP binnenhalen',
    'create_email'                                                                     => 'Maak e-mailbericht',
    'email_address'                                                                    => 'E-mailadres',
    'email_name'                                                                       => 'E-mail naam',
    'help_topic'                                                                       => 'Help Onderwerp',
    'auto_response'                                                                    => 'Automatisch beantwoorden',
    'host_name'                                                                        => 'Hostnaam',
    'port_number'                                                                      => 'Poortnummer',
    'mail_box_protocol'                                                                => 'Mailbox Protocol',
    'authentication_required'                                                          => 'Authenticatie vereist',
    'yes'                                                                              => 'Ja',
    'no'                                                                               => 'Nee',
    'header_spoofing'                                                                  => 'Header Spoofing',
    'allow_for_this_email'                                                             => 'Sta toe voor dit e-mailbericht',
    'imap_config'                                                                      => 'IMAP configuratie',
    'email_information_and_settings'                                                   => 'E-mail informatie en instellingen',
    'incoming_email_information'                                                       => 'Inkomende e-mail informatie ',
    'outgoing_email_information'                                                       => 'Uitgaande e-mail informatie',
    'new_ticket_settings'                                                              => 'Nieuw ticket instellingen',
    'protocol'                                                                         => 'Protocol',
    'fetching_protocol'                                                                => 'Ophaal protocol',
    'transfer_protocol'                                                                => 'Transfer protocol',
    'from_name'                                                                        => 'Van naam',
    'add_an_email'                                                                     => 'Voeg een e-mailbericht toe',
    'edit_an_email'                                                                    => 'Bewerk een e-mailbericht',
    'disable_for_this_email_address'                                                   => 'Schakel uit voor dit e-mailbericht',
    'validate_certificates_from_tls_or_ssl_server'                                     => 'Verifeer SSL/TLS certificaten van server',
    'authentication'                                                                   => 'Authenticatie',
    'incoming_email_connection_failed_please_check_email_credentials_or_imap_settings' => 'Inkomende e-mail verbinding mislukt. Controleer de instellingen.',
    'outgoing_email_connection_failed'                                                 => 'Uitgaande e-mail verbinding mislukt',
    'you_cannot_delete_system_default_email'                                           => 'Je kan geen standaard e-mailberichten verwijderen',
    'email_deleted_sucessfully'                                                        => 'E-mail succesvol verwijderd',
    'email_can_not_delete'                                                             => 'E-mail kan niet verwijderd worden',
    'outgoing_email_failed'                                                            => 'Uitgaand e-mailbericht mislukt',
    /*
      |--------------------------------------
      |  Ban Emails Create Page
      |--------------------------------------
     */
    'ban_lists'                        => 'Blokkeringslijst',
    'ban_email'                        => 'Blokkeer E-mailadres',
    'ban_status'                       => 'Blokkeringsstatus',
    'list_of_banned_emails'            => 'Lijst van geblokkeerde e-mailadressen',
    'edit_banned_email'                => 'Bewerk geblokkeerde e-mailadressen',
    'create_a_banned_email'            => 'Maak een geblokkeerd e-mailadres',
    'email_banned_sucessfully'         => 'E-mailadres succes geblokkeerd',
    'email_can_not_ban'                => 'E-mailadres kan niet worden geblokkeerd',
    'banned_email_updated_sucessfully' => 'Geblokkeerd e-mailadres succesvol bijgewerkt',
    'banned_email_not_updated'         => 'Geblokkeerd e-mailadres niet bijgewerkt',
    'banned_removed_sucessfully'       => 'Blokkering succesvol verwijderd',
    /*
      |--------------------------------------
      |  Templates Index Page
      |--------------------------------------
     */
    'templates'                                => 'Sjabloon',
    'template_set'                             => 'Sjabloon groep',
    'create_template'                          => 'Maak sjabloon',
    'edit_template'                            => 'Bewerk sjabloon',
    'list_of_templates_sets'                   => 'Lijst van sjabloon groepen',
    'create_set'                               => 'Maak groep',
    'template_name'                            => 'Sjabloon naam',
    'template_saved_successfully'              => 'Sjabloon succesvol opgeslagen',
    'template_updated_successfully'            => 'Sjabloon succesvol bijgewerkt',
    'in_use'                                   => 'In gebruik',
    'you_have_created_a_new_template_set'      => 'Je hebt een nieuwe sjabloon groep gemaakt',
    'you_have_successfully_activated_this_set' => 'Je hebt deze groep succesvol geactiveerd',
    'template_set_deleted_successfully'        => 'Sjabloon groep succesvol verwijderd',
    //Template Description
    'Create ticket agent'       => 'E-mail welke naar een medewerker en beheerder wordt verstuurd wanneer er een ticket is gemaakt',
    'Assign ticket'             => 'Ticket toegewezen aan medewerker',
    'Create ticket'             => 'E-mail welke naar een klant wordt gestuurd als het ticket succesvol is aangemaakt.',
    'Check ticket'              => 'Als een klant de status van een ticket wil controleren, wordt er een e-mailbericht naar de klant gestuurd. Deze link kan de klant gebruiken om de status te bekijken zonder daarvoor in te hoeven loggen.',
    'Ticket reply agent'        => 'Een melding versturen naar de medewerker wanneer de klant reageert op het ticket',
    'Registration notification' => 'Gebruikersnaam en wachtwoord worden verstuurd bij de registratie',
    'Reset password'            => 'E-mailbericht met een wachtwoord reset link',
    'Error report'              => 'Fouten rapportage',
    'Ticket creation'           => 'Melding welke verstuurd word door het systeem als een klant een ticket heeft gemaakt',
    'Ticket reply'              => 'Als een medewerker antwoord op een ticket wordt er een e-mail melding verstuurd naar de klant en deelnemers',
    'Close ticket'              => 'E-mail welke verstuurd wordt naar een klant als het ticket gesloten wordt.',
    'Create ticket by agent'    => 'Een medewerker maakt een ticket voor de klant in naam van de klant.',
    /*
      |--------------------------------------
      |  Templates Create Page
      |--------------------------------------
     */
    'template_set_to_clone' => 'Sjabloon geselecteerd om te klonen',
    'language'              => 'Taal',
    /*
      |--------------------------------------
      |  Diagnostics Page
      |--------------------------------------
     */
    'diagnostics'                => 'Diagnostiek',
    'from'                       => 'Van',
    'to'                         => 'Naar',
    'subject'                    => 'Onderwerp',
    'message'                    => 'Bericht',
    'send'                       => 'Verstuur',
    'choose_an_email'            => 'Kies een e-mailades',
    'email_diagnostic'           => 'E-mail Diagnostiek',
    'send-mail-to-diagnos'       => 'Stuur een bericht om de uitgaande instellingen te controleren',
    'message_has_been_sent'      => 'Bericht is verstuurd',
    'message_sent_from_php_mail' => 'Bericht verstuurd van PHP-Mail',
    'mailer_error'               => 'Mail Fout',
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
    'country-code'                 => 'Land-code',
    'company'                      => 'Bedrijf',
    'company_settings'             => 'Bedrijf instellingen',
    'website'                      => 'Website',
    'phone'                        => 'Telefoonnummer',
    'address'                      => 'Adres',
    'landing'                      => 'Landing pagina',
    'offline'                      => 'Offline pagina',
    'thank'                        => 'Bedankt pagina',
    'logo'                         => 'Logo',
    'save'                         => 'Opslaan',
    'delete-logo'                  => 'Verwijder logo',
    'click-delete'                 => 'Klik hier om te verwijderen',
    'use_logo'                     => 'Gebruik logo',
    'company_updated_successfully' => 'Bedrijf succesvol bijgewerkt',
    'company_can_not_updated'      => 'Bedrijf kan niet worden bijgewerkt',
    'enter-country-phone-code'     => 'Voer de landcode in',
    'country-code-required-error'  => 'Land-code is vereist met een telefoonnummer.',
    'incorrect-country-code-error' => 'Ongeldige land-code.',
    /*
      |--------------------------------------
      |   System Settings Page
      |--------------------------------------
     */
    'system'                                                     => 'Systeem',
    'online'                                                     => 'Online',
    'offline'                                                    => 'Offline',
    'name/title'                                                 => 'Naam / Titel',
    'pagesize'                                                   => 'Pagina grootte ',
    'url'                                                        => 'URL',
    'default_department'                                         => 'Standaard afdeling',
    'loglevel'                                                   => 'Rapportage niveau',
    'purglog'                                                    => 'Verwijder rapportages',
    'nameformat'                                                 => 'Naam formaat',
    'timeformat'                                                 => 'Tijd formaat',
    'date'                                                       => 'Datum',
    'dateformat'                                                 => 'Datum formaat',
    'date_time'                                                  => 'Datum en tijd formaat',
    'day_date_time'                                              => 'Dag,datum en tijd formaat',
    'timezone'                                                   => 'Standaard tijd zone',
    'Ticket-created-successfully'                                => 'Ticket succesvol gemaakt',
    'Ticket-created-successfully2'                               => 'Het ticket is aangemaakt maar niet gecontroleerd. Het wordt getoond in de inbox als de klant zijn account heeft bevestigd.',
    'system_updated_successfully'                                => 'Systeem succesvol bijgewerkt',
    'system_can_not_updated'                                     => 'Systeem kan niet worden bijgewerkt',
    'ticket_updated_successfully'                                => 'Ticket succesvol bijgewerkt',
    'ticket_can_not_updated'                                     => 'Ticket kan niet bijgewerkt worden',
    'email_updated_successfully'                                 => 'E-mailadres succesvol bijgewerkt',
    'email_can_not_updated'                                      => 'E-mailadres kan niet bijgewerkt worden',
    'select_a_time_zone'                                         => 'Selecteer een tijd zone',
    'select_a_date_time_format'                                  => 'Selecteer een datum tijd formaat',
    'Ticket-has-been-created-successfully-your-ticket-number-is' => 'Het ticket is succesvol aangemaakt. Je ticketnummer is',
    'Please-save-this-for-future-reference'                      => 'Bewaar dit voor toekomstige referentie',
    'email-moble-already-taken'                                  => 'Het e-mailadres of mobiele nummer is al in gebruik',
    'mobile-has-been-taken'                                      => 'Mobiele nummer is al in gebruik',
    'failed-to-create-user-tcket-as-mobile-has-been-taken'       => 'Niet gelukt om een ticket te maken want het telefoonnummer welke je hebt ingevuld hoort bij een ander account, maar de andere informatie welke je hebt ingevuld komen niet overeen met die gebruiker. Controleer de gegevens of registreer je als nieuwe gebruiker',
    'rtl'                                                        => 'RTL (Rechts naar Links)',
    'the_rtl_support_is_only_applicable_to_the_outgoing_mails'   => 'De ondersteuning van RTL is alleen van toepassing op uitgaande e-mailberichten',
    'user_set_ticket_status'                                     => 'Sta gebruikers toe om de status van een ticket te wijzigen',
    'send_otp_for_account_verfication'                           => 'Stuur OTP naar gebruikers',
    'otp_usage_info'                                             => 'We sturen een bevestigingslink en een OTP code naar een gebruiker als je niet gecontroleerde gebruikers toe staat om een ticket te maken. Als het e-mailadres niet verplicht is dan ontvangen gebruikers de gebruikersnaam en het wachtwoord op hun mobiele telefoonnummer( Hiervoor wordt gebruik gemaakt van de Faveo SMS Plugin).',
    'send_otp_title_message'                                     => 'Stuur OTP code voor account controle, wachtwoord resetten en de bevestiging van het mobiele telefoonnummer',
    'allow_unverified_users_to_create_ticket'                    => 'Sta niet gecontroleerde gebruikers toe om een ticket te maken',
    'make-email-mandatroy'                                       => 'Verplicht het e-mailadres bij het maken van een ticket / gebruiker',
    'email_man_info'                                             => 'Als je het e-mailadres niet verplicht maakt kunnen gebruikers registeren zonder e-mailadres. We readen niet aan om niet bevestigde gebruikers tickets te laten maken, zodat deze meldingen kunnen ontvangen op hun mobiele telefoonnummer en op hun account kunnen inloggen met het wachtwoord en e-mailadres welke ze op hun mobiele nummer hebben ontvangen..',
    /*
      |--------------------------------------
      |   Email Settings Page
      |--------------------------------------
     */
    'email'                               => 'E-mail',
    'email-settings'                      => 'E-mail instellingen',
    'default_template'                    => 'Standaard sjabloon groep:',
    'default_system_email'                => 'Standaard systeem e-mailadres:',
    'default_alert_email'                 => 'Standaard waarschuwings e-mailadres:',
    'admin_email'                         => 'Beheer e-mailadres:',
    'email_fetch'                         => 'E-mail ophalen:',
    'enable'                              => 'Inschakelen',
    'default_MTA'                         => 'Standaard MTA',
    'fetch_auto-corn'                     => 'Binnenhalen bij geplande taak',
    'strip_quoted_reply'                  => 'Verwijder gequote antwoord',
    'reply_separator'                     => 'Antwoord scheidingsteken',
    'accept_all_email'                    => 'Accepteer alle e-mailberichten',
    'accept_email_unknown'                => 'Accepteer e-mailberichten van onbekende gebruikers',
    'accept_email_collab'                 => 'Accepteer e-mail medewerkers',
    'automatically_and_collab_from_email' => 'Medewekers automatisch toevoegen van e-mailvelden',
    'default_alert_email'                 => 'Standaard e-mail melding',
    'attachments'                         => 'Bijlagen',
    'email_attahment_user'                => 'E-mail bijlagen naar de gebruiker',
    'cron_notification'                   => 'Inschakelen meldingstaak',
    'cron'                                => 'Taken inplannen',
    'cron-jobs'                           => 'Ingeplande taken',
    'crone-url-message'                   => 'Dit is de taakplanner URL voor je systeem.',
    'clipboard-copy-message'              => 'Gekopieerd naar klembord.',
    'click'                               => 'Klik hier',
    'check-cron-set'                      => 'Om te bekijken hoe je een taakplanner op je server kan istellen.',
    'notification-email'                  => 'E-mailmeldingen',
    'click-url-copy'                      => 'Klik hier om de URL te kopiëren',
    'job-scheduler-error'                 => 'Taakplanner kan niet bijgewerkt worden.',
    'job-scheduler-success'               => 'Taakplanner succesvol bijgewerkt.',
    /*

      |--------------------------------------
      |   Ticket Settings Page
      |--------------------------------------

     */

    'ticket'                             => 'Ticket',
    'ticket-setting'                     => 'Ticket instellingen',
    'default_ticket_number_format'       => 'Standaard ticket nummer formaat',
    'default_ticket_number_sequence'     => 'Standaard ticket volgorde',
    'default_status'                     => 'Standaard Status',
    'default_priority'                   => 'Standaard prioriteit',
    'default_sla'                        => 'Standaard SLA',
    'default_help_topic'                 => 'Standaard Help Onderwerp',
    'maximum_open_tickets'               => 'Maximaal aantal open tickets',
    'agent_collision_avoidance_duration' => 'Tijd voordat medewerkers tegelijkertijd aan hetzelfde ticket werken',
    'human_verification'                 => 'Menselijke verificatie',
    'claim_on_response'                  => 'Claim ticket bij antwoord',
    'assigned_tickets'                   => 'Toegewezen tickets',
    'answered_tickets'                   => 'Beantwoorde tickets',
    'agent_identity_masking'             => 'Identiteit medewerker verbergen',
    'enable_HTML_ticket_thread'          => 'S`chakel HTML ticket thread in',
    'allow_client_updates'               => 'Sta client updates toe',
    'lock_ticket_frequency'              => 'Blokkeer tickets',
    'only-once'                          => 'Eenmaal',
    'frequently'                         => 'Hoevaak',
    'reload-now'                         => 'Vernieuwen',
    'ticket-lock-inactive'               => 'U bent een tijdje inactief geweest. Herlaad de pagina.',
    'make-system-default-mail'           => 'Maak dit het standaard systeem e-mailadres',
    'thread'                             => 'Thread',

    /*

      |--------------------------------------
      |   Access Settings Page
      |--------------------------------------

     */

    'access'                                           => 'Toegang',
    'expiration_policy'                                => 'Wachtwoord verval beleid',
    'allow_password_resets'                            => 'Sta wachtwoord reset toe',
    'reset_token_expiration'                           => 'Herstel token vervaltijd',
    'agent_session_timeout'                            => 'Medewerker sessie timeout',
    'bind_agent_session_IP'                            => 'Wijs de sessie van de medewerker toe aan het IP-adres',
    'registration_required'                            => 'Registratie benodigd',
    'require_registration_and_login_to_create_tickets' => 'Registratie benodigd en inloggen om tickets te creëren',
    'registration_method'                              => 'Registratie methode',
    'user_session_timeout'                             => 'Gebruikers sessie time-out',
    'client_quick_access'                              => 'Gebruiker snelle toegang',
    'cron'                                             => 'Taak',
    'cron_settings'                                    => 'Taak instellingen',
    'system-settings'                                  => 'Systeem Instellingen',
    'settings-2'                                       => 'Instellingen',

    /*

      |--------------------------------------
      |   Auto-Response Settings Page
      |--------------------------------------

     */

    'auto_responce'                      => 'Autmomatisch antwoord',
    'auto_responce-settings'             => 'Autmomatisch antwoord instellingen',
    'new_ticket'                         => 'Nieuw ticket',
    'new_ticket_by_agent'                => 'Nieuw ticket door medewerker',
    'new_message'                        => 'Nieuw bericht',
    'submitter'                          => 'Aanvrager: ',
    'send_receipt_confirmation'          => 'Verstuur ontvangstbevestiging',
    'participants'                       => 'Deelnemers: ',
    'send_new_activity_notice'           => 'Stuur nieuwe activiteit notificatie',
    'overlimit_notice'                   => 'Bestandgrootte overschreden melding',
    'email_attachments_to_the_user'      => 'E-mail bijlagen naar nieuwe gebruiker',
    'auto_response_updated_successfully' => 'Automatisch antwoord succesvol bijgewerkt',
    'auto_response_can_not_updated'      => 'Automatisch antwoord kan niet bijgewerkt worden',

    /*

      |--------------------------------------
      |   Alert & Notice Settings Page
      |--------------------------------------

     */

    'disable'                                               => 'Uitschakelen',
    'admin_email_2'                                         => 'Beheerder E-mail',
    'alert_notices'                                         => 'Waarschuwingen & Meldingen',
    'alert_notices_setitngs'                                => 'Waarschuwingen & Meldingen instellingen',
    'new_ticket_alert'                                      => 'Nieuw ticket melding',
    'department_manager'                                    => 'Afdelingsmanager',
    'department_members'                                    => 'Afdelingdeelnemers',
    'organization_account_manager'                          => 'Organisatie accountmanager',
    'new_message_alert'                                     => 'Nieuw bericht melding',
    'last_respondent'                                       => 'Laatste beantwoorder',
    'assigned_agent_team'                                   => 'Toegewezen medewerker / team',
    'new_internal_note_alert'                               => 'Nieuwe interne notitie melding',
    'ticket_assignment_alert'                               => 'Ticket toewijzing melding',
    'team_lead'                                             => 'Teamleider',
    'team_members'                                          => 'Teamdeelnemers',
    'ticket_transfer_alert'                                 => 'Ticket verplaatst melding',
    'overdue_ticket_alert'                                  => 'Ticket over tijd melding ',
    'system_alerts'                                         => 'Systeem melding',
    'system_errors'                                         => 'Systeemfouten',
    'SQL_errors'                                            => 'SQL fouten',
    'excessive_failed_login_attempts'                       => 'Te vaak verkeerd ingelogd',
    'system_error_reports'                                  => 'Systeem foutrapporten',
    'Send_app_crash_reports_to_help_Ladybird_improve_Faveo' => 'Verstuur crash rapportages om Ladybird te helpen met het verbeteren van Faveo',
    'alert_&_notices_updated_successfully'                  => 'Waarschuwingen & Meldingen succesvol geupdate',
    'alert_&_notices_can_not_updated'                       => 'Waarschuwing & Meldingen kunnen niet worden geupdate',

    /*

      |-----------------------------------------------
      | Ratings Instellingen
      |-----------------------------------------------

     */

    'current_ratings' => 'Huidige waardering',
    'edit_ratings'    => 'Wijzig waardering',

    /*

      |------------------------------------------------
      | Language page
      |------------------------------------------------

     */

    'default'            => 'Standaard',
    'language-settings'  => 'Taal instellingen',
    'iso-code'           => 'ISO-CODE',
    'download'           => 'Downloaden',
    'upload_file'        => 'Upload bestand',
    'enter_iso-code'     => 'Voer ISO-CODE in',
    'eg.'                => 'Voorbeeld',
    'for'                => 'Voor',
    'english'            => 'Engels',
    'language-name'      => 'Taal naam',
    'file'               => 'Bestand',
    'read-more'          => 'Lees meer',
    'enable_lang'        => 'Inschakelen',
    'add-lang-package'   => 'Voeg nieuw taalpakket toe',
    'package_exist'      => 'Pakket bestaat al',
    'iso-code-error'     => 'Fout in ISO-CODE. Voer de juiste ISO-CODE in.',
    'zipp-error'         => 'Fout in .zip bestand. Zip moet alleen de PHP taalbestanden bevatten.',
    'upload-success'     => 'Uploaden gelukt',
    'file-error'         => 'Fout in bestand of verkeerd bestand.',
    'delete-success'     => 'Taalpakket succesvol verwijderd.',
    'lang-doesnot-exist' => 'Taalpakket bestaat niet',
    'active-lang-error'  => 'Taalpakket kan niet verwijderd worden als deze actief is.',
    'language-error'     => 'Taalpakket niet gevonden in uw LANG map.',
    'lang-fallback-lang' => 'De standaard taal kan niet verwijderd worden,',

    /*

      |--------------------------------------
      | Plugin Settings
      |--------------------------------------

     */

    'add_plugin'            => 'Plugin toevoegen',
    'plugins'               => 'Plugins',
    'upload'                => 'Uploaden',
    'plugins-list'          => 'Lijst van plugins',
    'plugin-exists'         => 'Plugin bestaat al',
    'plugin-installed'      => 'Plugin succesvol geinstalleerd.',
    'plugin-path-missing'   => 'Plugin bestandspad bestaat niet',
    'no-plugin-file'        => 'Er is geen ',
    'plugin-config-missing' => 'Er is geen <b>config.php of ServiceProvider.php</b>',
    'plugin-info'           => 'Ben je een programmeur? We moedigen je aan om je eigen plugins te schrijven en deze beschikbaar te maken voor de community.',
    'plugin-info-pro'       => 'Om de plugins te bekijken welke beschikbaar zijn bij de PRO versie van Faveo;',
    'click-here'            => 'Klik hier',

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
    'manage' => 'Beheren',
    /*
      |--------------------------------------
      |  Help Topic index Page
      |--------------------------------------
     */
    'help_topics'       => 'Help-onderwerpen',
    'topic'             => 'Onderwerp',
    'type'              => 'Type',
    'priority'          => 'Prioriteit',
    'last_updated'      => 'Laatst bijgewerkt',
    'create_help_topic' => 'Maak help-onderwerp',
    'action'            => 'Actie',
    /*
      |--------------------------------------
      |  Help Topic Create Page
      |--------------------------------------
     */
    'active'                                => 'Actief',
    'disabled'                              => 'Uitgeschakeld',
    'public'                                => 'Publiek',
    'private'                               => 'Privé',
    'parent_topic'                          => 'Hoofdonderwerp',
    'Custom_form'                           => 'Aangepast formulier',
    'SLA_plan'                              => 'SLA Plan',
    'sla-plans'                             => 'SLA Plannen',
    'auto_assign'                           => 'Automatisch toewijzen',
    'auto_respons'                          => 'Automatisch antwoorden',
    'ticket_number_format'                  => 'Ticket nummer formaat',
    'system_default'                        => 'Systeem standaard',
    'custom'                                => 'Aangepast',
    'internal_notes'                        => 'Interne notities',
    'select_a_parent_topic'                 => 'Selecteer een hoofdonderwerp',
    'custom_form'                           => 'Aangepast formulier',
    'select_a_form'                         => 'Selecteer een formulier',
    'select_a_department'                   => 'Selecteer een afdeling',
    'departments'                           => 'Afdelingen',
    'select_a_priority'                     => 'Selecteer een prioriteit',
    'priorities'                            => 'Prioriteiten',
    'select_a_sla_plan'                     => 'Selecteer een SLA plan',
    'sla_plans'                             => 'SLA Plannen',
    'select_an_agent'                       => 'Selecteer een medewerker',
    'helptopic_created_successfully'        => 'Help-onderwerp succesvol aangemaakt',
    'helptopic_can_not_create'              => 'Help-onderwerp kan niet worden aangemaakt',
    'helptopic_updated_successfully'        => 'Help-onderwerp succesvol bijgewerkt',
    'helptopic_can_not_update'              => 'Help-onderwerp kan niet worden bijgewerkt',
    'you_cannot_delete_default_department'  => 'Je kan de standaardafdeling niet verwijderen',
    'have_been_moved_to_default_help_topic' => 'Is verplaatst naar standaard help-onderwerp',
    'helptopic_deleted_successfully'        => 'Help-onderwerp succesvol verwijderd',
    'make-default-helptopic'                => 'Maak hiervan het standaard help-onderwerp',
    /*
      |--------------------------------------
      |  SLA plan Index Page
      |--------------------------------------
     */
    'sla_plans'    => 'SLA plannen',
    'create_SLA'   => 'Maak een SLA',
    'grace_period' => 'Tijdsduur',
    'added_date'   => 'Datum toegevoegd',
    /*
      |--------------------------------------
      |  SLA plan Create Page
      |--------------------------------------
     */
    'transient'                                            => 'Toegankelijk',
    'ticket_overdue_alert'                                 => 'Ticket over tijd meldingen',
    'sla_plan_created_successfully'                        => 'SLA plan succesvol gemaakt',
    'sla_plan_can_not_create'                              => 'SLA plan kan niet worden aangemaakt',
    'sla_plan_updated_successfully'                        => 'SLA plan succesvol bijgewerkt',
    'sla_plan_can_not_update'                              => 'SLA plan kan niet bijgewerkt worden',
    'you_cannot_delete_default_department'                 => 'Je kan de standaard afdeling niet verwijderen',
    'have_been_moved_to_default_sla'                       => 'Is verplaatst naar de standaard SLA',
    'associated_department_have_been_moved_to_default_sla' => 'Gerelateerde afdeling is verplaatst naar standaard SLA',
    'associated_help_topic_have_been_moved_to_default_sla' => 'Gerelateerd help-onderwerp is verplaatst naar standaard SLA',
    'sla_plan_deleted_successfully'                        => 'SLA Plan succesvol verwijderd',
    'sla_plan_can_not_delete'                              => 'SLA Plan kan niet verwijderd worden',
    'make-default-sla'                                     => 'Maak hier het standaard SLA plan van',
    /*
      |--------------------------------------
      |  Work Flow
      |--------------------------------------
     */
    'workflow'                      => 'Werkstroom',
    'ticket_workflow'               => 'Ticket werkstroom',
    'create_workflow'               => 'Maak werkstroom',
    'edit_workflow'                 => 'Aanpassen werkstroom',
    'updated'                       => 'Bijgewerkt',
    'target'                        => 'Doel',
    'target_channel'                => 'Doelkanaal',
    'execution_order'               => 'Uitvoer volgorde',
    'target_channel'                => 'Doelkanaal',
    'workflow_rules'                => 'Werkstroom regels',
    'workflow_action'               => 'Werkstroom actie',
    'rules'                         => 'Regels',
    'order'                         => 'Volgorde',
    'condition'                     => 'Voorwaarde',
    'statement'                     => 'Verklaring',
    'select_a_channel'              => 'Selecteer een kanaal',
    'body'                          => 'Hoofdtekst',
    'select_one'                    => 'Selecteer één',
    'equal_to'                      => 'Gelijk aan',
    'not_equal_to'                  => 'Niet gelijk aan',
    'contains'                      => 'Bevat',
    'does_not_contain'              => 'Bevat niet',
    'starts_with'                   => 'Begint met',
    'ends_with'                     => 'Eindigd met',
    'select_an_action'              => 'Selecteer een actie',
    'reject_ticket'                 => 'Ticket weigeren',
    'set_department'                => 'Afdeling instellen',
    'set_priority'                  => 'Prioriteit instellen',
    'set_sla_plan'                  => 'SLA plan instellen',
    'assign_team'                   => 'Toegewezen team',
    'assign_agent'                  => 'Toegewezen medewerker',
    'set_help_topic'                => 'Helponderwerp instellen',
    'set_ticket_status'             => 'Ticket status instellen',
    'workflow_created_successfully' => 'Werkstroom succesvol aangemaakt',
    'workflow_updated_successfully' => 'Werkstroom succesvol bijgewerkt',
    'workflow_deleted_successfully' => 'Werkstroom succesvol verwijderd',
    /*
      |--------------------------------------
      |  Form Create Page
      |--------------------------------------
     */
    'title'                                 => 'Titel',
    'instruction'                           => 'Instructie',
    'label'                                 => 'Label',
    'visibility'                            => 'Zichtbaarheid',
    'variable'                              => 'Variabel',
    'create_form'                           => 'Formulier maken',
    'forms'                                 => 'Formulieren',
    'form_name'                             => 'Naam formulier',
    'view_this_form'                        => 'Bekijk dit formulier',
    'delete_from'                           => 'Verwijder formulier',
    'are_you_sure_you_want_to_delete'       => 'Weet je zeker dat je dit wil verwijderen?',
    'close'                                 => 'Sluiten',
    'instructions'                          => 'Instructies',
    'instructions_on_creating_form'         => 'Selecteer hieronder welk soort veld je wil toevoegen. Vergeet niet om de veldopties in te stellen en scheidt de waarden met een komma. Als je klaar bent met het maken van het formulier kun je deze opslaan door te kiezen voor: Formulier opslaan',
    'form_properties'                       => 'Eigenschappen formulier',
    'adding_fields'                         => 'Velden toevoegen',
    'click_add_fields_button_to_add_fields' => "Klik op de knop <b>'Velden toevoegen'</b> om een veld toe te voegen",
    'add_fields'                            => 'Velden toevoegen',
    'save_form'                             => 'Formulier opslaan',
    'label'                                 => 'Label',
    'name'                                  => 'Naam',
    'type'                                  => 'Type',
    'values(selected_fields)'               => 'Waarden(geselecteerde velden)',
    'required'                              => 'Vereist',
    'Action'                                => 'Actie',
    'remove'                                => 'Verwijder',
    'form_deleted_successfully'             => 'Formulier succesvol verwijderd',
    'successfully_created_form'             => 'Formulier succesvol aangemaakt',
    'please_fill_form_name'                 => 'Vul de naam voor het formulier in',
    'category_inserted_successfully'        => 'Categorie succesvol ingevoegd',
    'category_not_inserted'                 => 'Categorie niet ingevoegd',
    'category_updated_successfully'         => 'Categorie succesvol bijgewerkt',
    'category_not_updated'                  => 'Categorie niet bijgewerkt',
    'category_deleted_successfully'         => 'Categorie succesvol verwijderd',
    'category_not_deleted'                  => 'Categorie niet verwijderd',
    'article_inserted_successfully'         => 'Artikel succesvol ingevoegd',
    'article_not_inserted'                  => 'Artikel niet ingevoegd',
    'article_updated_successfully'          => 'Artikel succesvol bijgewerkt',
    'article_not_updated'                   => 'Artikel niet bijgewerkt',
    'article_deleted_successfully'          => 'Artikel succesvol verwijderd',
    'article_not_deleted'                   => 'Artikel niet verwijderd',
    'article_can_not_deleted'               => 'Artikel kan niet verwijderd worden',
    'page_created_successfully'             => 'Pagina succesvol aangemaakt',
    'your_page_updated_successfully'        => 'Pagina succesvol bijgewerkt',
    'page_deleted_successfully'             => 'Pagina succesvol verwijderd',
    'settings_updated_successfully'         => 'Instellingen succesvol bijgewerkt',
    'settings_can_not_updated'              => 'Instellingen kunnen niet worden bijgewerkt',
    'can_not_process'                       => 'Kan niet verwerken',
    'comment_published'                     => 'Reactie gepubliceerd',
    'comment_deleted'                       => 'Reactie verwijderd',
    'publish_time'                          => 'Publicatie tijd',
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
    'themes' => "Thema's",
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
    'ok'             => 'Oké',
    'cancel'         => 'Annuleren',
    'select-ticket'  => 'Selecteer Tickets.',
    'confirm'        => 'Weet je het zeker?',
    'delete-tickets' => 'Verwijder tickets',
    'close-tickets'  => 'Tickets sluiten',
    'open-tickets'   => 'Tickets openen',
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
    'are_you_sure'       => 'Weet je het zeker',
    'staffs'             => 'Medewerkers',
    'name'               => 'Naam',
    'user_name'          => 'Gebruikersnaam',
    'status'             => 'Status',
    'group'              => 'Groep',
    'department'         => 'Afdeling',
    'created'            => 'Aangemaakt',
    'lastlogin'          => 'Laatste login',
    'createagent'        => 'Maak een account voor een medewerker aan',
    'delete'             => 'Verwijderen',
    'agents'             => 'Medewerkers',
    'create'             => 'Maken',
    'edit'               => 'Bewerken',
    'departments'        => 'Afdelingen',
    'groups'             => 'Groepen',
    'select_a_time_zone' => 'Selecteer een tijdzone',
    'time_zones'         => 'Tijdzones',
    /*
      |--------------------------------------
      |  Staff Create Page
      |--------------------------------------
     */
    'create_agent'                            => 'Maak medeweker',
    'first_name'                              => 'Voornaam',
    'last_name'                               => 'Achternaam',
    'mobile_number'                           => 'Mobiele telefoonnummer',
    'agent_signature'                         => 'Handtekening medewerker',
    'account_status_setting'                  => 'Account Status & Instellingen',
    'account_type'                            => 'Account Type',
    'admin'                                   => 'Beheerder',
    'agent'                                   => 'Medewerker',
    'account_status'                          => 'Account Status',
    'locked'                                  => 'Vastgezet',
    'assigned_group'                          => 'Toegewezen groep',
    'primary_department'                      => 'Hoofd afdeling',
    'agent_time_zone'                         => 'Tijdzone medewerker',
    'day_light_saving'                        => 'Zomertijd',
    'limit_access'                            => 'Beperk toegang',
    'directory_listing'                       => 'Adresboek',
    'vocation_mode'                           => 'Vakantie modus',
    'assigned_team'                           => 'Toegewezen team',
    'agent_send_mail_error_on_agent_creation' => 'Er ging iets mis tijdens het versturen van e-mail naar de medewerker. Controleer de e-mail instellingen en probeer het opnieuw',
    'agent_creation_success'                  => 'Medewerker succesvol aangemaakt',
    'failed_to_create_agent'                  => 'Niet gelukt om medewerker aan te maken',
    'failed_to_edit_agent'                    => 'Niet gelukt om medewerker aan te passen',
    'agent_updated_sucessfully'               => 'Medewerker succesvol bijgewerkt',
    'unable_to_update_agent'                  => 'Niet gelukt om medewerker bij te werken',
    'agent_deleted_sucessfully'               => 'Medewerker succesvol verwijderd',
    'this_staff_is_related_to_some_tickets'   => 'Deze medewerker is gekoppeld aan tickets',
    'list_of_agents'                          => 'Lijst van medewerkers',
    'create_an_agent'                         => 'Maak een medewerker',
    'edit_an_agent'                           => 'Bewerk een medewerker',
    /*
      |--------------------------------------
      |  Department Create Page
      |--------------------------------------
     */
    'create_department'                                => 'Maak afdeling',
    'manager'                                          => 'Manager',
    'ticket_assignment'                                => 'Ticket toewijzig',
    'restrict_ticket_assignment_to_department_members' => 'Beperk ticket toewijzing tot afdeling medewerkers',
    'outgoing_emails'                                  => 'Uitgaande e-mail',
    'outgoing_email'                                   => 'Binnenkomende e-mail',
    'template_set'                                     => 'Sjabloon groep',
    'auto_responding_settings'                         => 'Automatisch antwoord instellingen',
    'disable_for_this_department'                      => 'Uitschakelen voor deze afdeling',
    'auto_response_email'                              => 'Automatisch antwoord e-mail',
    'recipient'                                        => 'Ontvanger',
    'group_access'                                     => 'Groepstoegang',
    'department_signature'                             => 'Handtekening afdeling',
    'list_of_departments'                              => 'Lijst met afdelingen',
    'create_a_department'                              => 'Maak een afdeling',
    'outgoing_email_settings'                          => 'Uitgaande e-mail instellingen',
    'edit_department'                                  => 'Bewerk afdeling',
    'select_a_sla'                                     => 'Selecteer een SLA',
    'select_a_manager'                                 => 'Select een manager',
    'department_created_sucessfully'                   => 'Afdeling succcesvol aangemaakt',
    'failed_to_create_department'                      => 'Niet gelukt om afdeling aan te maken',
    'department_updated_sucessfully'                   => 'Afdeling succesvol bijgewerkt',
    'department_not_updated'                           => 'Afdeling niet bijgewerkt',
    'you_cannot_delete_default_department'             => 'Je kan de standaard afdeling niet verwijderen',
    'have_been_moved_to_default_department'            => 'is verplaatst naar de standaard afdeling',
    'the_associated_helptopic_has_been_deactivated'    => 'Het gerelateerde help-onderwerp is uitgeschakeld',
    'department_deleted_sucessfully'                   => 'Afdeling succesvol verwijderd',
    'department_can_not_delete'                        => 'Afdeling kan niet verwijderd worden',
    'make-default-department'                          => 'Maak hier de standaard afdeling van',
    /*
      |--------------------------------------
      |  Team Create Page
      |--------------------------------------
     */
    'create_team'                => 'Maak team',
    'team_lead'                  => 'Teamleider',
    'assignment_alert'           => 'Opdracht melding',
    'disable_for_this_team'      => 'Uitschakelen voor dit team',
    'teams'                      => 'Teams',
    'list_of_teams'              => 'Lijst met teams',
    'create_a_team'              => 'Maak een team',
    'edit_a_team'                => 'Bewerk een team',
    'teams_created_successfully' => 'Team succcesvol aangemaakt',
    'teams_can_not_create'       => 'Team kan niet aangemaakt worden',
    'teams_updated_successfully' => 'Team succesvol bijgewerkt',
    'teams_can_not_update'       => 'Team kan niet bijgewerkt worden',
    'teams_deleted_successfully' => 'Team succesvol verwijderd',
    'teams_can_not_delete'       => 'Teams kunnen niet verwijderd worden',
    'select_a_team'              => 'Selecteer een team',
    'select_a_team_lead'         => 'Selecteer een teamleider',
    'members'                    => 'Leden',
    /*
      |--------------------------------------
      |  Group Create Page
      |--------------------------------------
     */
    'create_group'                                                                           => 'Maak een groep',
    'goups'                                                                                  => 'Groepen',
    'can_create_ticket'                                                                      => 'Kan een ticket maken',
    'can_edit_ticket'                                                                        => 'Kan een ticket bewerken',
    'can_post_ticket'                                                                        => 'Kan een ticket plaatsen',
    'can_close_ticket'                                                                       => 'Kan een ticket sluiten',
    'can_assign_ticket'                                                                      => 'Kan een ticket toewijzen',
    'can_transfer_ticket'                                                                    => 'Kan een ticket verplaatsen',
    'can_delete_ticket'                                                                      => 'Kan een ticket verwijderen',
    'can_ban_emails'                                                                         => 'Kan een e-mailadres blokkeren',
    'can_manage_premade'                                                                     => 'Kan vooraf gemaakte antwoorden beheren',
    'can_manage_FAQ'                                                                         => 'Kan de veel gestelde vragen beheren',
    'can_view_agent_stats'                                                                   => 'Kan medewerker statistieken bekijken',
    'department_access'                                                                      => 'Afdelingstoegang',
    'admin_notes'                                                                            => 'Beheerder notities',
    'group_members'                                                                          => 'Groepsleden',
    'group_name'                                                                             => 'Naam van de groep',
    'select_a_group'                                                                         => 'Selecteer een groep',
    'create_a_group'                                                                         => 'Maak een groep',
    'edit_a_group'                                                                           => 'Bewerk een groep',
    'group_created_successfully'                                                             => 'Groep succesvol aangemaakt',
    'group_can_not_create'                                                                   => 'Groep kan niet aangemaakt worden',
    'group_updated_successfully'                                                             => 'Groep succcesvol bijgewerkt',
    'group_can_not_update'                                                                   => 'Groep kan niet worden bijgewerkt',
    'there_are_agents_assigned_to_this_group_please_unassign_them_from_this_group_to_delete' => 'Er zijn medewekers toegewezen aan deze groep. Verwijder deze eerst van de groep om de groep te kunnen verwijderen',
    'group_cannot_delete'                                                                    => 'Groep kan niet verwijderd worden',
    'group_deleted_successfully'                                                             => 'Groep succesvol verwijderd',
    'group_cannot_delete'                                                                    => 'Groep kan niet verwijderd worden',
    'failed_to_load_the_page'                                                                => 'Niet gelukt om de pagina te laden',
    /*
      |--------------------------------------
      |  SMTP Page
      |--------------------------------------
     */
    'driver'     => 'Driver',
    'smtp'       => 'SMTP',
    'host'       => 'Host',
    'port'       => 'Poort',
    'encryption' => 'Versleuteling',
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
    'agent_panel'        => 'Medewerkerpaneel',
    'profile'            => 'Profiel',
    'change_password'    => 'Wachtwoord veranderen',
    'sign_out'           => 'Uitloggen',
    'Tickets'            => 'TICKETS',
    'ticket-details'     => 'Ticket Details',
    'inbox'              => 'Inbox',
    'my_tickets'         => 'Mijn Tickets',
    'unassigned'         => 'Niet toegewezen',
    'trash'              => 'Prullenbak',
    'Updates'            => 'UPDATES',
    'no_new_updates'     => 'Geen Updates',
    'check_for_updates'  => 'Controleer op updates',
    'update-version'     => 'Versie bijwerken',
    'open'               => 'Open',
    'inprogress'         => 'In behandeling',
    'inprogress_tickets' => 'Tickets in behandeling Tickets',
    'closed'             => 'Gesloten',
    'Departments'        => 'AFDELINGEN',
    'tools'              => 'Instellingen',
    'canned'             => 'Standaard',
    'knowledge_base'     => 'Kennisbank',
    'kb-settings'        => 'Kennisbank instellingen',
    'loading'            => 'Laden',
    'ratings'            => 'Beoordelingen',
    'please_rate'        => 'Beoordeel alsjeblieft:',
    'ticket_ratings'     => 'TICKET BEOORDELING',
    /*
      |-----------------------------------------------
      |  Ticket
      |-----------------------------------------------
     */
    'ticket_created_successfully'                                        => 'Ticket succesvol gemaakt',
    'failed_to_create_a_new_ticket'                                      => 'Niet gelukt om een nieuw ticket te maken',
    'your_ticket_have_been_closed'                                       => 'Je ticket is gesloten',
    'your_ticket_have_been_resolved'                                     => 'Je ticket is opgelost',
    'your_ticket_have_been_opened'                                       => 'Je ticket is geopend',
    'your_ticket_have_been_moved_to_trash'                               => 'Je ticket is verplaatst naar de prullenbak',
    'this_email_have_been_banned'                                        => 'Dit e-mailadres is geblokkeerd',
    'ticket_updated_successfully'                                        => 'Ticket succesvol bijgewerkt',
    'you_have_successfully_replied_to_your_ticket'                       => 'Je hebt succesvol op dit ticket geantwoord',
    'for_some_reason_your_message_was_not_posted_please_try_again_later' => 'Er heeft zich een fout voor gedaan, je bericht is niet verstuurd. Probeer het later nog eens',
    'for_some_reason_your_reply_was_not_posted_please_try_again_later'   => 'Er heeft zich een fout voor gedaan, je bericht is niet verstuurd. Probeer het later nog eens',
    'you_have_unassigned_your_ticket'                                    => 'Je hebt niet toegewezen tickets',
    'for_some_reason_your_request_failed'                                => 'Er heeft zich een fout voorgedaan, het is niet gelukt je verzoek te voltooien',
    'trash-delete-ticket'                                                => 'Tickets definitief verwijderen',
    'trash-delete-title-msg'                                             => 'Klik hier om de tickets definitief te verwijderen',
    'moved_to_trash'                                                     => 'Verplaats de geselecteerde tickets naar de prullenbak',
    'tickets_have_been_closed'                                           => 'De geselecteerde tickets zijn gesloten',
    'tickets_have_been_opened'                                           => 'De geselecteerde tickets zijn geopend',
    'unable_to_fetch_emails'                                             => 'Niet gelukt om e-mail binnen te halen',
    'reply_content_is_a_required_field'                                  => 'Inhoud van het antwoord is een vereist veld',
    'internal_content_is_a_required_field'                               => 'Interne inhoud is een vereist veld',
    /*
      |-----------------------------------------------
      |  Profile
      |-----------------------------------------------
     */
    'view-profile'                      => 'Bekijk profiel',
    'edit-profile'                      => 'Bewerk profiel',
    'user_information'                  => 'Gebruikersinformatie',
    'time_zone'                         => 'Tijdzone',
    'phone_number'                      => 'Telefoonnummer',
    'contact_information'               => 'Contactinformatie',
    'Profile-Updated-sucessfully'       => 'Profiel succesvol bijgewerkt.',
    'User-profile-Updated-Successfully' => 'Gebruikersprofiel succesvol bijgewerkt.',
    'User-Created-Successfully'         => 'Gebruiker succesvol aangemaakt.',
    /*
      |-----------------------------------------------
      |  Dashboard
      |-----------------------------------------------
     */
    'dashboard'         => 'Dashboard',
    'line_chart'        => 'Grafiek',
    'statistics'        => 'Statistieken',
    'opened'            => 'Geopend',
    'resolved'          => 'Opgelost',
    'closed'            => 'Gesloten',
    'deleted'           => 'Verwijderd',
    'start_date'        => 'Startdatum',
    'end_date'          => 'Einddatum',
    'filter'            => 'Filter',
    'report'            => 'Rapportages',
    'Legend'            => 'Leganda',
    'total'             => 'Totaal',
    'dashboard_reports' => 'Dashboard rapportages',
    /*
      |------------------------------------------------
      |User Page
      |------------------------------------------------
     */
    'user_credentials'                                 => 'Gebruikersgegevens',
    'user_directory'                                   => 'Gebruikersmap',
    'ban'                                              => 'Blokkeer',
    'user'                                             => 'Gebruiker',
    'users'                                            => 'Gebruikers',
    'create_user'                                      => 'Maak gebruiker',
    'edit_user'                                        => 'Bewerk gebruiker',
    'full_name'                                        => 'Volledige naam',
    'mobile'                                           => 'Mobiele telefoonnummer',
    'last_login'                                       => 'Laatste Login',
    'user_profile'                                     => 'Gebruikersprofiel',
    'assign'                                           => 'Toewijzen',
    'open_tickets'                                     => 'Open tickets',
    'closed_tickets'                                   => 'Gesloten tickets',
    'deleted_tickets'                                  => 'Verwijderde tickets',
    'user_created_successfully'                        => 'Gebruiker succesvol aangemaakt',
    'user_updated_successfully'                        => 'Gebruiker succesvol bijgewerkt',
    'profile_updated_sucessfully'                      => 'Profiel succesvol bijgewerkt',
    'password_updated_sucessfully'                     => 'Wachtwoord succesvol bijgewerkt',
    'password_was_not_updated_incorrect_old_password'  => 'Het wachtwoord is niet bijgewerkt. Het oude wachtwoord is onjuist.',
    'the_user_has_been_removed_from_this_organization' => 'De gebruiker is verwijderd uit deze organisatie',
    'user_report'                                      => 'Gebruiker rapportage',
    'send_password_via_email'                          => 'Verstuurd wachtwoord via e-mail',
    'user_send_mail_error_on_user_creation'            => 'Er heeft zich een fout voor gedaan bij het versturen van de e-mail naar de gebruiker. Controleer de instellingen en probeer het opnieuw.',
    'country_code'                                     => 'Land code',
    /*
      |------------------------------------------------
      |Organization Page
      |------------------------------------------------
     */
    'organizations'                     => 'Organisaties',
    'organization'                      => 'Organisatie',
    'organization_list'                 => 'Lijst met organisaties',
    'view_organization_profile'         => 'Bekijk organisatie profiel',
    'create_organization'               => 'Maak organisatie',
    'account_manager'                   => 'Account Manager',
    'update'                            => 'Bijwerken',
    'please_select_an_organization'     => 'Selecteer een organisatie',
    'please_select_an_user'             => 'Selecteer een gebruiker',
    'organization_profile'              => 'Organisatie profiel',
    'organization-s_head'               => 'Organisatie directeur',
    'select_department_manager'         => 'Selecteer afdelingsmanager',
    'select_organization_manager'       => 'Selecteer organisatiemanager',
    'users_of'                          => 'Gebruikers van',
    'organization_created_successfully' => 'Organisatie succesvol aangemaakt',
    'organization_can_not_create'       => 'Organization kan niet aangemaakt worden',
    'organization_updated_successfully' => 'Organisatie succesvol bijgewerkt',
    'organization_can_not_update'       => 'Organization kan niet worden bijgewerkt',
    'organization_deleted_successfully' => 'Organisatie succesvol verwijderd',
    'report_of'                         => 'Rapportage van',
    'ticket_of'                         => 'Tickets van',
    /*
      |----------------------------------------------
      |  Ticket page
      |----------------------------------------------
     */
    'subject'                                        => 'Onderwerp',
    'ticket_id'                                      => 'Ticket ID',
    'priority'                                       => 'Prioriteit',
    'from'                                           => 'Formulier',
    'last_replier'                                   => 'Laatste beantwoorder',
    'assigned_to'                                    => 'Toegewezen aan',
    'last_activity'                                  => 'Laatste activiteit',
    'answered'                                       => 'Beantwoord',
    'assigned'                                       => 'Toegewezen',
    'create_ticket'                                  => 'Maak ticket',
    'tickets'                                        => 'Tickets',
    'open'                                           => 'Open',
    'Ticket_Information'                             => 'TICKET INFORMATIE',
    'Ticket_Id'                                      => 'TICKET ID',
    'User'                                           => 'GEBRUIKER',
    'Unassigned'                                     => 'NIET TOEGEWEZEN',
    'unassigned-tickets'                             => 'Niet toegewezen tickets',
    'generate_pdf'                                   => 'Maak PDF',
    'change_status'                                  => 'Verander status',
    'more'                                           => 'Meer',
    'delete_ticket'                                  => 'Verwijder ticket',
    'emergency'                                      => 'Noodgeval',
    'high'                                           => 'Hoog',
    'medium'                                         => 'Middel',
    'low'                                            => 'Laag',
    'sla_plan'                                       => 'SLA plan',
    'created_date'                                   => 'Aanmaak datum',
    'due_date'                                       => 'Verloop datum',
    'last_response'                                  => 'Laatste antwoord',
    'source'                                         => 'Bron',
    'last_message'                                   => 'Laatste bericht',
    'reply'                                          => 'Beantwoorden',
    'response'                                       => 'Antwoord',
    'reply_content'                                  => 'Inhoud antwoord',
    'attachment'                                     => 'Bijlage',
    'internal_note'                                  => 'Interne notitie',
    'this_ticket_is_under_banned_user'               => 'Dit ticket is van een geblokkeerde gebruiker',
    'ticket_source'                                  => 'Ticket aangemaakt',
    'are_you_sure_to_ban'                            => 'Weet je zeker dat je wil blokkeren',
    'whome_do_you_want_to_assign_ticket'             => 'Aan wie wil je het ticket toewijzen',
    'are_you_sure_you_want_to_surrender_this_ticket' => 'Weet je zeker dat je dit ticket wil opgeven',
    'add_collaborator'                               => 'Hulp van iemand inschakelen',
    'search_existing_users'                          => 'Zoek bestaande gebruikers',
    'add_new_user'                                   => 'Nieuwe gebruiker toevoegen',
    'search_existing_users_or_add_new_users'         => 'Zoek bestaande gebruiker of voeg gebruiker toe',
    'search_by_email'                                => 'Zoeken met e-mailadres',
    'list_of_collaborators_of_this_ticket'           => 'Lijst met mensen welke helpen met dit ticket',
    'submit'                                         => 'Verzenden',
    'max'                                            => 'Maximaal',
    'add_cc'                                         => 'CC toevoegen',
    'recepients'                                     => 'Ontvangers',
    'select_a_canned_response'                       => 'Selecteer een standaard antwoord',
    'assign_to'                                      => 'Toewijzen aan',
    'detail'                                         => 'Detail',
    'user_details'                                   => 'Gebruikerdetails',
    'ticket_option'                                  => 'Ticket Opties',
    'ticket_detail'                                  => 'Ticketdetails',
    'Assigned_To'                                    => 'Toegewezen aan',
    'locked-ticket'                                  => 'Waarschuwing! Dit ticket is vastgezet door ',
    'minutes-ago'                                    => 'Minuten geleden',
    'access-ticket'                                  => 'Waarschuwing, dit ticket is vastgezet door jou voor de volgende ',
    'minutes'                                        => ' minuten',
    'in_minutes'                                     => 'In minuten',
    'add_another_owner'                              => 'Een andere eigenaar toevoegen',
    'user-not-found'                                 => 'Gebruiker niet gevonden, probeer het nog eens of voeg een nieuwe gebruiker toe.',
    'change-success'                                 => 'Gelukt! De eigenaar van het ticket is veranderd.',
    'user-exists'                                    => 'Gebruiker bestaat al. Zoek naar bestaande gebruikers.',
    'valid-email'                                    => 'Voer een geldig e-mailadres in.',
    'search_user'                                    => 'Zoek gebruiker',
    'merge-ticket'                                   => 'Ticket samenvoegen',
    'title'                                          => 'Titel',
    'merge'                                          => 'Samenvoegen',
    'select_tickets'                                 => 'Selecteer tickets om samen te voegen',
    'select-pparent-ticket'                          => 'Select hoofd ticket',
    'merge-reason'                                   => 'Reden voor samenvoegen',
    'no-reason'                                      => 'Er is geen reden opgegevend.',
    'get_merge_message'                              => 'Dit ticket is samengevoegd met ticket',
    'ticket_merged'                                  => ' is samengevoegd met dit ticket.',
    'no-tickets-to-merge'                            => 'Er zijn geen tickets meer van dezelfde eigenaar.',
    'merge-error'                                    => 'Kon je verzoek niet verwerken, probeer het later nog eens.',
    'merge-success'                                  => 'Tickets succesvol samen gevoegd.',
    'merge-error2'                                   => 'Selecteer de tickets om samen te voegen.',
    'select-tickets-to merge'                        => 'Selecteer twee of meer tickets om samen te voegen.',
    'different-users'                                => 'Tickets van verschillende gebruikers',
    'clean-up'                                       => 'Voorgoed verwijderen',
    'hard-delete-success-message'                    => 'Tickets zijn voorgoed verwijderd.',
    'overdue'                                        => 'Over tijd',
    'overdue-tickets'                                => 'Tickets over tijd',
    'change_owner_for_ticket'                        => 'Verander eigenaar voor ticket',
    /*
      |------------------------------------------------
      |Tools Page
      |------------------------------------------------
     */
    'canned_response'           => 'Standaard antwoorden',
    'create_canned_response'    => 'Maak standaard antwoord',
    'surrender'                 => 'Opgeven',
    'added_successfully'        => 'Succesvol toegevoegd',
    'updated_successfully'      => 'Succesvol bijgewerkt',
    'user_deleted_successfully' => 'Gebruiker succesvol verwijderd',
    'view'                      => 'Weergeven',
    /*
      |-----------------------------------------------
      | Main text
      |-----------------------------------------------
     */
    'copyright'           => 'Copyright',
    'all_rights_reserved' => 'Alle rechten voorbehouden',
    'powered_by'          => 'Mogelijk gemaakt door',
    'version'             => 'Version',
    /*
      |------------------------------------------------
      |Guest-User Page
      |------------------------------------------------
     */
    'issue_summary'             => 'Probleem samenvatting',
    'contact'                   => 'Contact',
    'issue_details'             => 'Kwestie details',
    'contact_informations'      => 'Contactiformatie',
    'contact_details'           => 'Contactdetails',
    'role'                      => 'Rol',
    'ext'                       => 'EXT',
    'profile_pic'               => 'Profielfoto',
    'agent_sign'                => 'Handtekening medewerker',
    'inactive'                  => 'Inactief',
    'male'                      => 'Man',
    'female'                    => 'Vrouw',
    'old_password'              => 'Oud Wachtwoord',
    'new_password'              => 'Nieuwe Wachtwoord',
    'confirm_password'          => 'Bevestig Wachtwoord',
    'gender'                    => 'Geslacht',
    'ticket_number'             => 'Ticket nummer',
    'content'                   => 'Inhoud',
    'edit_template'             => 'Bewerk sjabloon',
    'edit_status'               => 'Bewerk status',
    'create_status'             => 'Maak status',
    'edit_details'              => 'Bewerk details',
    'edit_templates'            => 'Bewerk sjablonen',
    'activate_this_set'         => 'Activeer deze groep',
    'show'                      => 'Toon',
    'no_notification_available' => 'Geen melding beschikbaar',
    //auto-close workflow
    'close-msg1'                                          => 'Het aantal dagen waarna het ticket automatisch gesloten moet worden.',
    'no_of_days'                                          => 'Aantal dagen',
    'close-msg2'                                          => 'Inschakelen automatisch sluiten werkstroom?',
    'enable_workflow'                                     => 'Inschaken werkstroom',
    'send_email_to_user'                                  => 'Verstuur e-mail naar gebruiker',
    'close-msg3'                                          => 'Selecteer een status voor het automatisch sluiten van een ticket.',
    'close-msg4'                                          => 'Verstuur e-mail naar de gebruiker bij het automatisch sluiten van een ticket?.',
    'edit_status'                                         => 'Bewerk status',
    'list_of_status'                                      => 'Lijst met statussen',
    'status_settings'                                     => 'Status Instellingen',
    'icon_class'                                          => 'Icoon class',
    'close_ticket_workflow'                               => 'Sluit ticket werkstroom',
    'ratings_settings'                                    => 'Beoordeling instellingen',
    'notification'                                        => 'Melding',
    'status_has_been_updated_successfully'                => 'Status is succesvol bijgewerkt',
    'status_has_been_created_successfully'                => 'Status is succesvol aangemaakt',
    'status_has_been_deleted'                             => 'Status is verwijderd',
    'you_cannot_delete_this_status'                       => 'Je kan deze status niet verwijderen',
    'you_have_deleted_all_the_read_notifications'         => 'Je hebt alle gelezen meldingen verwijderd',
    'you_have_deleted_all_the_notification_records_since' => 'Je hebt alle meldingen verwijderd welke aanwezig waren sinds ',
    'ratings_updated_successfully'                        => 'Beoordelingen succesvol bijgewerkt',
    'ratings_can_not_be_created'                          => 'Kan geen beoordelingen maken',
    'successfully_created_this_rating'                    => 'Succesvol deze beoordeling gemaakt',
    'rating_deleted_successfully'                         => 'Beoordeling succesvol verwijderd',
    //status msg
    'status_msg1'                          => 'Als je kiest voor JA dan wordt de gebruiker op de hoogte gebracht.',
    'notify_user'                          => 'Breng de klant op de hoogte van deze status?',
    'deleted_status'                       => 'Is dit een status waarbij het ticket verwijderd is',
    'resolved_status'                      => 'Is dit een status waarbi het ticket opgelost is',
    'status_msg3'                          => 'Als je kiest voor JA dan wordt de ticketstatus ingesteld als opgelost.',
    'status_msg2'                          => 'Als je kiest voor JA dan wordt de ticketstatus ingesteld als verwijderd.',
    'rating-msg2'                          => 'Selecteer een afdeling waarbij er beoordelingen gegeven kunnen worden. Als je geen afdeling selecteer dan kan er bij elke afdeling een beoordeling gegeven worden.',
    'rating-msg3'                          => 'Als je kiest voor JA kan een gebruiker de beoordeling wijzigen.',
    'rating_restrict'                      => 'Beperk beoordeling tot een afdeling',
    'rating_change'                        => 'Sta gebruiker toe de beoordeling te wijzigen?',
    'security_msg1'                        => 'Het bericht welke getoond work als een gebruiker teveel mislukte inlogpogingen heeft gedaan.',
    'security_msg2'                        => 'Het aantal loginpogingen welke een gebruiker heeft voordat hij wordt buitengesloten. Stel in op 0 om de loginpogingen wel bij te houden, maar de gebruiker niet buiten te sluiten',
    'security_msg3'                        => 'Het aantal minuten dat de gebruiker wordt buitengesloten bij teveel mislukte inlogpogingen.',
    'max_attempt'                          => 'Maximaal aantal login pogingen per gebruiker',
    'rating-msg1'                          => 'De maximale beoordeling welke gegeven kan worden. Voorbeeld: als je 5 kiest, is 1 de slechtste beoordeling, en 5 de beste beoordeling',
    'enter_no_of_days'                     => 'Voer het aantal dagen in',
    'template-types'                       => 'Sjabloon types',
    'close-workflow'                       => 'Sluit ticket werkstroom',
    'template'                             => 'Sjabloon',
    'rating_label'                         => 'Beoordeling label',
    'display_order'                        => 'Weergave volgorde',
    'rating_scale'                         => 'Beoordeling schaal',
    'rating_area'                          => 'Beoordeling ruimte',
    'modify'                               => 'Bewerk',
    'rating_name'                          => 'Beoordeling naam',
    'add_user_to_this_organization'        => 'Voeg een gebruiker toe aan deze organisatie',
    'Tickets_of'                           => 'Tickets van',
    'security'                             => 'Beveiliging',
    'security_settings'                    => 'Beveiligingsinstellingen',
    'lockouts'                             => 'Buitensluiten',
    'security_settings_saved_successfully' => 'Beveiligingsinstellingen succesvol opgeslagen',
    'manage_status'                        => 'Statussen beheren',
    'notifications'                        => 'Meldingen',
    'auto_close_workflow'                  => 'Automatisch sluiten werkstroom',
    'close_ticket_workflow_settings'       => 'Ticket sluiten werkstroom instellingen',
    'successfully_saved_your_settings'     => 'Instellingen succesvol opgeslagen',
    /*
      |------------------------------------------------
      |   Notification Settings Pages
      |------------------------------------------------
     */
    'notification_settings'                       => 'Meldingsinstellingen',
    'delete_noti'                                 => 'Verwijder alle gelezen meldingen?',
    'noti_msg1'                                   => 'Aantal dagen waarvan het meldingslogboek verwijderd kan worden',
    'noti_msg2'                                   => 'Je kan het aantal dagen invoeren waarvan het logboek verwijderd kan worden. De meldingen worden verwijderd vanaf de opgegeven datum.',
    'del_all_read'                                => 'Verwijder alle gelezen',
    'You_have_deleted_all_the_read_notifications' => 'Je hebt alle gelezen meldingen verwijderd',
    'view_all_notifications'                      => 'Bekijk alle meldingen',
    /*
      |------------------------------------------------
      |   Error Pages
      |------------------------------------------------
     */
    'not_found'                                       => 'Niet gevonden',
    'oops_page_not_found'                             => 'Oeps! Pagina niet gevonden',
    'we_could_not_find_the_page_you_were_looking_for' => 'We kunnen de pagina niet vinden',
    'internal_server_error'                           => 'Interne server fout',
    'be_right_back'                                   => 'We zijn zo terug',
    'sorry'                                           => 'Sorry',
    'we_are_working_on_it'                            => 'We zijn er mee bezig',
    'category'                                        => 'Categorie',
    'addcategory'                                     => 'Categorie toevoegen',
    'allcategory'                                     => 'Alle categorieën',
    'article'                                         => 'Artikel',
    'articles'                                        => 'Artikelen',
    'addarticle'                                      => 'Artikel toevoegen',
    'allarticle'                                      => 'Alle Artikelen',
    'pages'                                           => "Pagina's",
    'addpages'                                        => 'Pagina toevoegen',
    'allpages'                                        => "Alle pagina's",
    'widgets'                                         => 'Widgets',
    'widget-settings'                                 => 'Widget Instellingen',
    'footer1'                                         => 'Footer 1',
    'footer2'                                         => 'Footer 2',
    'footer3'                                         => 'Footer 3',
    'footer4'                                         => 'Footer 4',
    'sidewidget1'                                     => 'Side Widget 1',
    'sidewidget2'                                     => 'Side Widget 2',
    'comments'                                        => 'Reacties',
    'comments-list'                                   => 'Lijst met reacties',
    'settings'                                        => 'Instellingen',
    'parent'                                          => 'Hoofd',
    'description'                                     => 'Beschrijving',
    'enter_the_description'                           => 'Voer de beschrijving in',
    'publish'                                         => 'Publiceren',
    'publish_immediately'                             => 'Onmiddelijk publiceren',
    'published'                                       => 'Gepubliceerd',
    'draft'                                           => 'Concept',
    'create_a_category'                               => 'Maak een categorie',
    'add'                                             => 'Toevoegen',
    'social'                                          => 'Social',
    'social-widget-settings'                          => 'Social widget instellingen',
    'comment'                                         => 'Reacties',
    'not_published'                                   => 'Niet gepubliceerd',
    'numberofelementstodisplay'                       => 'Aantal elementen om weer te geven',
    //======================================
    'language'                                                                 => 'Taal',
    'save'                                                                     => 'Opslaan',
    'create'                                                                   => 'Maken',
    'dateformat'                                                               => 'Datum formaat',
    'slug'                                                                     => 'Slug',
    'read_more'                                                                => 'Lees meer',
    'view_all'                                                                 => 'Bekijk alles',
    'categories'                                                               => 'Categorieën',
    'need_more_support'                                                        => 'Heb je meer hulp nodig',
    'if_you_did_not_find_an_answer_please_raise_a_ticket_describing_the_issue' => 'Als je het antwoord niet hebt kunnen vinden, maak dan een ticket aan',
    'have_a_question?_type_your_search_term_here'                              => 'Heb je een vraag? Vul hier je zoekterm in...',
    'search'                                                                   => 'Zoeken',
    'frequently_asked_questions'                                               => 'Veelgestelde vragen',
    'leave_a_reply'                                                            => 'Laat een reactie achter',
    'post_message'                                                             => 'Plaats bericht',
    /*
      |--------------------------------------------------------------------------------------
      |  Client Panel [English(en)]
      |--------------------------------------------------------------------------------------
      | The following language lines are used in all Agent Panel related issues to translate
      | some words in view to English. You are free to change them to anything you want to
      | customize your views to better match your application.
      |
     */
    'home'                                                                             => 'Home',
    'submit_a_ticket'                                                                  => 'Ticket versturen',
    'my_profile'                                                                       => 'Mijn Profiel',
    'log_out'                                                                          => 'Uitloggen',
    'forgot_password'                                                                  => 'Wachtwoord vergeten',
    'create_account'                                                                   => 'Maak Account',
    'you_are_here'                                                                     => 'Je bent hier',
    'have_a_ticket'                                                                    => 'Heb je een ticket',
    'check_ticket_status'                                                              => 'Bekijk ticket status',
    'choose_a_help_topic'                                                              => 'Kies een help-onderwerp',
    'ticket_status'                                                                    => 'Ticket status',
    'post_comment'                                                                     => 'Plaats reactie',
    'plugin'                                                                           => 'Plugin',
    'edit_profile'                                                                     => 'Bewerk profiel',
    'Send'                                                                             => 'Verstuur',
    'no_article'                                                                       => 'Geen artikel',
    'profile_settings'                                                                 => 'Profiel instellingen',
    'please_fill_all_required_feilds'                                                  => 'Vul alle vereiste velden in.',
    'successfully_replied'                                                             => 'Succesvol geantwoord',
    'please_fill_some_data'                                                            => 'Voer de velden in!',
    'profile_updated_sucessfully'                                                      => 'Profiel succesvol bijgewerkt',
    'password_updated_sucessfully'                                                     => 'Wachtwoord succesvol bijgewerkt',
    'password_was_not_updated_incorrect_old_password'                                  => 'Wachtwoord niet bijgewerkt. Het oude wachtwoord klopt niet',
    'there_is_no_such_ticket_number'                                                   => 'Dit ticket nummer bestaat niet',
    "email_didn't_match_with_ticket_number"                                            => 'E-mail adres hoort niet bij dit ticket nummer',
    'we_have_sent_you_a_link_by_email_please_click_on_that_link_to_view_ticket'        => 'We hebben een link naar je e-mailadres gestuurd. Klik op de link in het bericht om je ticket te bekijken',
    'no_records_on_publish_time'                                                       => 'Geen informatie gevonden op dit tijdstip',
    'your_details_send_to_system'                                                      => 'Je informatie is naar het systeem verzonden',
    'your_details_can_not_send_to_system'                                              => 'Je informatie kan niet naar het systeem worden verstuurd',
    'your_comment_posted'                                                              => 'Je reactie is geplaatst',
    'sorry_not_processed'                                                              => 'Sorry, niet verwerkt',
    'profile_updated_sucessfully'                                                      => 'Profiel succcesvol bijgewerkt',
    'password_was_not_updated'                                                         => 'Wachtwoord is niet bijgewerkt',
    'sorry_your_ticket_token_has_expired_please_try_to_resend_the_ticket_link_request' => 'Sorry, je ticket token is verlopen! Vraag de link om het ticket te bekijken opnieuw aan',
    'sorry_you_are_not_allowed_token_expired'                                          => 'Sorry, niet toegestaan. Token verlopen!',
    'thank_you_for_your_rating'                                                        => 'Bedankt voor de beoordeling!',
    'your_ticket_has_been'                                                             => 'Je ticket is',
    'failed_to_send_email_contact_administrator'                                       => 'Versturen e-mail mislukt. Neem contact op met de beheerder',
    /*
     * |---------------------------------------------------------------------------------------
      |Api settings
     * |----------------------------------------------------------------------------------
     * |The following lanuage line used to get english traslation of api settings in admin panel
     * |
     * |
     */
    'webhooks'                         => 'Webhooks',
    'enter_url_to_send_ticket_details' => 'Voer de URL in om de ticket details naar toe te sturen',
    'api'                              => 'Api',
    'api_key'                          => 'Api Sleutel',
    'api_key_mandatory'                => 'Api sleutel vereist',
    'api_configurations'               => 'Api configuratie',
    'generate_key'                     => 'Maak sleutel',
    'api_settings'                     => 'API Instellingen',
    /*
     * -----------------------------------------------------------------------------
     * Error log and debugging settings
     * --------------------------------------------------------------------------
     *
     */
    'error-debug'                        => 'Foutrapportages en foutopsporingsmodus',
    'debug-options'                      => 'Foutopsporing opties',
    'view-logs'                          => 'Bekijk foutrapportages',
    'not-authorised-error-debug'         => 'Je hebt geen machtiging om deze URL te bekijken',
    'error-debug-settings'               => 'Foutopsporing instellingen',
    'debugging'                          => 'Foutopsporingsmodus',
    'bugsnag-debugging'                  => 'Stuur crashrapportages naar Ladybird om Faveo te helpen verbeteren',
    'error-debug-settings-saved-message' => 'Je foutrapportage en foutopsporingsmodus instellingen zijn succesvol opgeslagen',
    'error-debug-settings-error-message' => 'Je hebt geen wijzigingen aangebracht in de instellingen.',
    'error-logs'                         => 'Foutrapportages',
    /* ---------------------------------------------------------------------------------------
     * Latest update 16-06-2016
     * -----------------------------------------------------------------------------------
     */
    'that_email_is not_available_in_this_system' => 'Dat e-emailbericht is niet beschikbaar in het systeem',
    'use_subject'                                => 'Gebruik onderwerp',
    'reopen'                                     => 'Heropen',
    'invalid_attempt'                            => 'Ongeldige poging',
    /* ---------------------------------------------------------------------------------------
     * Latest update 27-07-2016
     * -----------------------------------------------------------------------------------
     */
    'queue'  => 'Wachtrij',
    'queues' => 'Wachtrijen',
    /*     * -------------------------------------------------------------------------------------------------
     * OTP  messages body to send to user while registering, resetting passwords
     * --------------------------------------------------------------------------------------------------
     */
    'hello'                   => 'Hallo',
    'reset-link-msg'          => ",\r\n Hier is de link om je wachtwoord mee te resetten.\r\n",
    'otp-for-your'            => ",\r\nOTP voor je",
    'account-verification-is' => 'account verificatie is',
    'extra-text'              => ".\r\nJe kan inloggen om je account te verifieren via OTP of je kan op de link klikken welke we naar je e-mailadres hebben verstuurd.",
    'otp-not-sent'            => 'We ondervinden wat problemen bij het versturen van de OTP code, probeer het later nog eens.',
    /*     * -------------------------------------------------------------------------------------------
     * Ticket number settings 03-08-2016
     * ------------------------------------------------------------------------------------------
     */
    'format'               => 'Formaat',
    'ticket-number-format' => 'Deze instelling wordt gebruikt om ticketnummers te maken. Gebruik hekje tekens (`#`) waar getallen moeten komen & dollar tekens(‘$’) op de plek waar letters moeten staan. Elk andere andere tekst welke je invoert blijft staan. ',
    'ticket-number-type'   => 'Kies een volgorde waarin nieuwe ticketnummers worden gemaakt. Het systeem heeft een oplopende en willekeurige volgorde als standaard',
    /*     * ----------------------------------------------------------------------------------------------------
     * Social media integration
     * ---------------------------------------------------------------------------------------------------------
     */
    'client_id'     => 'Klant ID',
    'client_secret' => 'Klant geheim',
    'redirect'      => 'Verwijs URL',
    'details'       => 'Details',
    'social-media'  => 'Social media',
    /*     * ----------------------------------------------------------------------------------------------
     * Report
     * ----------------------------------------------------------------------------------------------
     */
    'report'              => 'Rapportage',
    'Report'              => 'RAPPORTAGE',
    'start_date'          => 'Startdatum',
    'end_date'            => 'Einddatum',
    'select'              => 'Selecteer',
    'generate'            => 'Genereer',
    'day'                 => 'Dag',
    'week'                => 'Week',
    'month'               => 'Maand',
    'Currnet_In_Progress' => 'MOMENTEEL IN BEHANDELING',
    'Total_Created'       => 'TOTAAL GEMAAKT',
    'Total_Reopened'      => 'TOTAAL HEROPEND',
    'Total_Closed'        => 'TOTAAL gesloten',
    'tabular'             => 'Tabular',
    'reopened'            => 'Heropend',
    /* ---------------------------------------------------------------------------------------
     * Ticket Priority
     * -----------------------------------------------------------------------------------
     */
    'ticket_priority'               => 'Ticket prioriteit',
    'priority'                      => 'Prioriteit',
    'priority_desc'                 => 'Prioriteits beschrijving',
    'priority_urgency'              => 'Prioriteits urgentie',
    'priority_id'                   => 'Prioriteits ID',
    'priority_color'                => 'Prioriteit kleur',
    'ispublic'                      => 'Is publiek',
    'is_default'                    => 'Als standaard',
    'create_ticket_priority'        => 'Maak ticket prioriteit',
    'agent_notes'                   => 'Medewerker notities',
    'select_priority'               => 'Selecteer prioriteit',
    'normal'                        => 'Normaal',
    'ispublic'                      => 'Zichtbaarheid',
    'make-default-priority'         => 'Maak standaard prioriteit',
    'priority_successfully_created' => 'Prioriteit succesvol gemaakt',
    'priority_successfully_updated' => 'Prioriteit succesvol bijgewerkt',
    'delete_successfully'           => 'Succesvol verwijderd',
    'user_priority_status'          => 'Gebruiker prioriteit status',

    /* --------------------------------------------------------------------------------------------
     * Approval Updated
     * --------------------------------------------------------------------------------------------
     */
    'approval'             => 'Goedkeuring',
    'approval_tickets'     => 'Goedgekeurde tickets',
    'approve'              => 'Goedkeuren',
    'approval_request'     => 'Goedkeurings verzoek',
    'approvel_ticket_list' => 'Goedkeurings ticket lijst',

    'approval_settings'                      => 'Instellingen goedkeuren',
    'close_all_ticket_for_approval'          => 'Alle tickets sluiten voor goedkeuren',
    'approval_settings-created-successfully' => 'Goedkeurings instellingen succesvol aangemaakt',

    /* --------------------------------------------------------------------------------------------
     * Followup Updated
     * --------------------------------------------------------------------------------------------
     */
    'followup'              => 'Opvolging',
    'followup_tickets'      => 'Opvolg tickets',
    'followup_Notification' => 'Opvolging melding',

    /*
      *--------------------------------------------------------------------------------------------
      *Updated 6-9-2016
      *---------------------------------------------------------------------------------------
      */
    'not-available' => 'Niet beschikbaar',
    /* --------------------------------------------------------------------------------------------
     * User Module
     * --------------------------------------------------------------------------------------------
     */
    'confirm_deletion'                                            => 'Verwijderen bevestigen',
    'delete_all_content'                                          => 'Verwijder alle inhoud',
    'agent_profile'                                               => 'Medewerker profiel',
    'change_role_to_admin'                                        => 'Verander naar beheerder',
    'change_role_to_user'                                         => 'Verander naar gebruiker',
    'change_role_to_agent'                                        => 'Verander naar medewerker',
    'change_password'                                             => 'Verander wachtwoord',
    'role_change'                                                 => 'Rol aanpassen',
    'password_generator'                                          => 'Wachtwoord generator',
    'depertment'                                                  => 'Afdeling',
    'duetoday'                                                    => 'Einddatum vandaag',
    'today-due_tickets'                                           => 'Ticket met een einddatum vandaag',
    'password_change_successfully'                                => 'Wachtwoord succesvol veranderd',
    'role_change_successfully'                                    => 'Rol succesvol veranderd',
    'user_delete_successfully'                                    => 'Gebruiker succesvol verwijderd',
    'agent_delete_successfully'                                   => 'Medewerker succesvol verwijderd',
    'select_another_user'                                         => 'Selecteer een andere gebruiker',
    'agent_delete_successfully_and_ticket_assign_to_another_user' => 'Medewerker succesvol verwijderd, het ticket is aan een andere gebruiker toegewezen',

    /************************************New updates*************************************/
    /*                             Translation Required                                 */
    /************************************************************************************/
    'deleted_user'              => 'Deleted User',
    'deleted_user_directory'    => 'Deleted User Directory',
    'restore'                   => 'Restore',
    'user_restore_successfully' => 'User restore successfully',

    /*** updates 28-11-2016***/
    'apply' => 'Apply',

    /* updates 2-12-2016 **/
    'sort-by'                      => 'Sort by',
    'created-at'                   => 'Created at',
    'or'                           => 'OR',
    'activate'                     => 'Activate',
    'system-email-not-configured'  => 'We are unable to process email request as the system has no configured email for sending mails. Please contact and report system admin.',
    'assign-ticket'                => 'Assign tickets',
    'can-not-inactive-group'       => 'Can not make the group inactive as it has agents assigned in it. Please assign those agents to another group and try again.',
    'internal-note-has-been-added' => 'Internal note added to the ticket',
    'active-users'                 => 'Active users',
    'deleted-users'                => 'Deleted users',
    'view-option'                  => 'View options',
    'accoutn-not-verified'         => 'User account is not verified',
    'enabled'                      => 'Enabled',
    'disabled'                     => 'Disabled',
    'user-account-is-deleted'      => 'This user account has been deleted.',
    'restore-user'                 => 'Restore user account',
    'delete-account-caution-info'  => 'Please note this account may still have  open tickets in the system.',
    'reply-can-not-be-empty'       => 'Reply can not be blank. Please enter your reply.',

    //update 18-12-2016
    'account-created-contact-admin-as-we-were-not-able-to-send-opt' => 'Your account has been created successfully. Please contact admin for account activation as we were not able to send you an OPT code.',

    //update 19-12-2016
    'only-agents'    => 'Agent users',
    'only-users'     => 'Clients users',
    'banned-users'   => 'Banned users',
    'inactive-users' => 'Inactive users',
    'all-users'      => 'All users',
    //update 21-12-2016
    'selected-user-is-already-the-owner' => 'Selected user is already the owner of this ticket.',
    //updated 15-5-2017
    'session-expired' => 'Session expired or invalid, please try again.',
    //updated 10-10-2023
    'what_should_be_done_with_content_owned_by_this_user' => 'Wat moet er gebeuren met de inhoud die eigendom is van deze gebruiker',
];
