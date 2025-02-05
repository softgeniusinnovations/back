<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FrontendsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('frontends')->insert([
            [
                'id' => 1,
                'data_keys' => 'seo.data',
                'data_values' => '{"seo_image":"1","keywords":["bet","betting","sports betting","spobet","odds","oddsrate"],"description":"Welcome to our sports betting platform. Explore a wide array of thrilling sports events and bet on your favorite teams to win big. Our user-friendly interface ensures a seamless experience, with secure transactions.","social_title":"BetLab - Sports Betting Platform","social_description":"Welcome to our sports betting platform. Explore a wide array of thrilling sports events and bet on your favorite teams to win big. Our user-friendly interface ensures a seamless experience, with secure transactions.","image":"64c5311c5c5011690644764.png"}',
                'created_at' => '2020-07-04 23:42:52',
                'updated_at' => '2023-07-29 15:32:44',
            ],
            [
                'id' => 2,
                'data_keys' => 'cookie.data',
                'data_values' => '{"short_desc":"We may use cookies or any other tracking technologies when you visit our website, including any other media form, mobile website, or mobile application related or connected to help customize the Site and improve your experience.","description":"<div class=\\"mb-5\\" style=\\"color: rgb(111, 111, 111); font-family: Nunito, sans-serif; margin-bottom: 3rem !important;\\"><h3 class=\\"mb-3\\" style=\\"font-weight: 600; line-height: 1.3; font-size: 24px; font-family: Exo, sans-serif; color: rgb(54, 54, 54);\\">What information do we collect?<\\/h3><p class=\\"font-18\\" style=\\"margin-right: 0px; margin-left: 0px; font-size: 18px !important;\\">We gather data from you when you register on our site, submit a request, buy any services, react to an overview, or round out a structure. At the point when requesting any assistance or enrolling on our site, as suitable, you might be approached to enter your: name, email address, or telephone number. You may, nonetheless, visit our site anonymously.<\\/p><\\/div><div class=\\"mb-5\\" style=\\"color: rgb(111, 111, 111); font-family: Nunito, sans-serif; margin-bottom: 3rem !important;\\"><h3 class=\\"mb-3\\" style=\\"font-weight: 600; line-height: 1.3; font-size: 24px; font-family: Exo, sans-serif; color: rgb(54, 54, 54);\\">How do we protect your information?<\\/h3><p class=\\"font-18\\" style=\\"margin-right: 0px; margin-left: 0px; font-size: 18px !important;\\">All provided delicate\\/credit data is sent through Stripe.<br>After an exchange, your private data (credit cards, social security numbers, financials, and so on) won\'t be put away on our workers.<\\/p><\\/div><div class=\\"mb-5\\" style=\\"color: rgb(111, 111, 111); font-family: Nunito, sans-serif; margin-bottom: 3rem !important;\\"><h3 class=\\"mb-3\\" style=\\"font-weight: 600; line-height: 1.3; font-size: 24px; font-family: Exo, sans-serif; color: rgb(54, 54, 54);\\">Do we disclose any information to outside parties?<\\/h3><p class=\\"font-18\\" style=\\"margin-right: 0px; margin-left: 0px; font-size: 18px !important;\\">We don\'t sell, exchange, or in any case move to outside gatherings by and by recognizable data. This does exclude confided in outsiders who help us in working our site, leading our business, or adjusting you, since those gatherings consent to keep this data private. We may likewise deliver your data when we accept discharge is suitable to follow the law, implement our site strategies, or ensure our own or others\' rights, property, or wellbeing.<\\/p><\\/div><div class=\\"mb-5\\" style=\\"color: rgb(111, 111, 111); font-family: Nunito, sans-serif; margin-bottom: 3rem !important;\\"><h3 class=\\"mb-3\\" style=\\"font-weight: 600; line-height: 1.3; font-size: 24px; font-family: Exo, sans-serif; color: rgb(54, 54, 54);\\">Children\'s Online Privacy Protection Act Compliance<\\/h3><p class=\\"font-18\\" style=\\"margin-right: 0px; margin-left: 0px; font-size: 18px !important;\\">We are consistent with the prerequisites of COPPA (Children\'s Online Privacy Protection Act), we don\'t gather any data from anybody under 13 years old. Our site, items, and administrations are completely coordinated to individuals who are in any event 13 years of age or more established.<\\/p><\\/div><div class=\\"mb-5\\" style=\\"color: rgb(111, 111, 111); font-family: Nunito, sans-serif; margin-bottom: 3rem !important;\\"><h3 class=\\"mb-3\\" style=\\"font-weight: 600; line-height: 1.3; font-size: 24px; font-family: Exo, sans-serif; color: rgb(54, 54, 54);\\">Changes to our Privacy Policy<\\/h3><p class=\\"font-18\\" style=\\"margin-right: 0px; margin-left: 0px; font-size: 18px !important;\\">If we decide to change our privacy policy, we will post those changes on this page.<\\/p><\\/div><div class=\\"mb-5\\" style=\\"color: rgb(111, 111, 111); font-family: Nunito, sans-serif; margin-bottom: 3rem !important;\\"><h3 class=\\"mb-3\\" style=\\"font-weight: 600; line-height: 1.3; font-size: 24px; font-family: Exo, sans-serif; color: rgb(54, 54, 54);\\">How long we retain your information?<\\/h3><p class=\\"font-18\\" style=\\"margin-right: 0px; margin-left: 0px; font-size: 18px !important;\\">At the point when you register for our site, we cycle and keep your information we have about you however long you don\'t erase the record or withdraw yourself (subject to laws and guidelines).<\\/p><\\/div><div class=\\"mb-5\\" style=\\"color: rgb(111, 111, 111); font-family: Nunito, sans-serif; margin-bottom: 3rem !important;\\"><h3 class=\\"mb-3\\" style=\\"font-weight: 600; line-height: 1.3; font-size: 24px; font-family: Exo, sans-serif; color: rgb(54, 54, 54);\\">What we don\\u2019t do with your data<\\/h3><p class=\\"font-18\\" style=\\"margin-right: 0px; margin-left: 0px; font-size: 18px !important;\\">We don\'t and will never share, unveil, sell, or in any case give your information to different organizations for the promoting of their items or administrations.<\\/p><\\/div>","status":1}',
                'created_at' => '2020-07-04 23:42:52',
                'updated_at' =>  '2022-03-30 11:23:12'
            ],
            [
                'id' => 3,
                'data_keys' => 'policy_pages.element',
                'data_values' => '{"title":"Privacy Policy","details":"<div class=\\"mb-5\\" style=\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\"><h3 class=\\"mb-3\\" style=\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\">What information do we collect?<\\/h3><p class=\\"font-18\\" style=\\"margin-right:0px;margin-left:0px;font-size:18px;\\">We gather data from you when you register on our site, submit a request, buy any services, react to an overview, or round out a structure. At the point when requesting any assistance or enrolling on our site, as suitable, you might be approached to enter your: name, email address, or telephone number. You may, nonetheless, visit our site anonymously.<\\/p><\\/div><div class=\\"mb-5\\" style=\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\"><h3 class=\\"mb-3\\" style=\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\">How do we protect your information?<\\/h3><p class=\\"font-18\\" style=\\"margin-right:0px;margin-left:0px;font-size:18px;\\">All provided delicate\\/credit data is sent through Stripe.<br \\/>After an exchange, your private data (credit cards, social security numbers, financials, and so on) won\'t be put away on our workers.<\\/p><\\/div><div class=\\"mb-5\\" style=\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\"><h3 class=\\"mb-3\\" style=\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\">Do we disclose any information to outside parties?<\\/h3><p class=\\"font-18\\" style=\\"margin-right:0px;margin-left:0px;font-size:18px;\\">We don\'t sell, exchange, or in any case move to outside gatherings by and by recognizable data. This does exclude confided in outsiders who help us in working our site, leading our business, or adjusting you, since those gatherings consent to keep this data private. We may likewise deliver your data when we accept discharge is suitable to follow the law, implement our site strategies, or ensure our own or others\' rights, property, or wellbeing.<\\/p><\\/div><div class=\\"mb-5\\" style=\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\"><h3 class=\\"mb-3\\" style=\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\">Children\'s Online Privacy Protection Act Compliance<\\/h3><p class=\\"font-18\\" style=\\"margin-right:0px;margin-left:0px;font-size:18px;\\">We are consistent with the prerequisites of COPPA (Children\'s Online Privacy Protection Act), we don\'t gather any data from anybody under 13 years old. Our site, items, and administrations are completely coordinated to individuals who are in any event 13 years of age or more established.<\\/p><\\/div><div class=\\"mb-5\\" style=\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\"><h3 class=\\"mb-3\\" style=\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\">Changes to our Privacy Policy<\\/h3><p class=\\"font-18\\" style=\\"margin-right:0px;margin-left:0px;font-size:18px;\\">If we decide to change our privacy policy, we will post those changes on this page.<\\/p><\\/div><div class=\\"mb-5\\" style=\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\"><h3 class=\\"mb-3\\" style=\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\">How long we retain your information?<\\/h3><p class=\\"font-18\\" style=\\"margin-right:0px;margin-left:0px;font-size:18px;\\">At the point when you register for our site, we cycle and keep your information we have about you however long you don\'t erase the record or withdraw yourself (subject to laws and guidelines).<\\/p><\\/div><div class=\\"mb-5\\" style=\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\"><h3 class=\\"mb-3\\" style=\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\">What we don\\u2019t do with your data<\\/h3><p class=\\"font-18\\" style=\\"margin-right:0px;margin-left:0px;font-size:18px;\\">We don\'t and will never share, unveil, sell, or in any case give your information to different organizations for the promoting of their items or administrations.<\\/p><\\/div>"}',
                'created_at' => '2020-07-04 23:42:52',
                'updated_at' =>  '2022-03-30 11:23:12'
            ],
            [
                'id' => 4,
                'data_keys' => 'policy_pages.element',
                'data_values' => '{"title":"Terms of Service","details":"<div class=\\"mb-5\\" style=\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\"><p class=\\"font-18\\" style=\\"margin-right:0px;margin-left:0px;font-size:18px;\\">We claim all authority to dismiss, end, or handicap any help with or without cause per administrator discretion. This is a Complete independent facilitating, on the off chance that you misuse our ticket or Livechat or emotionally supportive network by submitting solicitations or protests we will impair your record. The solitary time you should reach us about the seaward facilitating is if there is an issue with the worker. We have not many substance limitations and everything is as per laws and guidelines. Try not to join on the off chance that you intend to do anything contrary to the guidelines, we do check these things and we will know, don\'t burn through our own and your time by joining on the off chance that you figure you will have the option to sneak by us and break the terms.<\\/p><\\/div><div class=\\"mb-5\\" style=\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\"><ul class=\\"font-18\\" style=\\"padding-left:15px;list-style-type:disc;font-size:18px;\\"><li style=\\"margin-top:0px;margin-right:0px;margin-left:0px;\\">Configuration requests - If you have a fully managed dedicated server with us then we offer custom PHP\\/MySQL configurations, firewalls for dedicated IPs, DNS, and httpd configurations.<\\/li><li style=\\"margin-top:0px;margin-right:0px;margin-left:0px;\\">Software requests - Cpanel Extension Installation will be granted as long as it does not interfere with the security, stability, and performance of other users on the server.<\\/li><li style=\\"margin-top:0px;margin-right:0px;margin-left:0px;\\">Emergency Support - We do not provide emergency support \\/ Phone Support \\/ LiveChat Support. Support may take some hours sometimes.<\\/li><li style=\\"margin-top:0px;margin-right:0px;margin-left:0px;\\">Webmaster help - We do not offer any support for webmaster related issues and difficulty including coding, &amp; installs, Error solving. if there is an issue where a library or configuration of the server then we can help you if it\'s possible from our end.<\\/li><li style=\\"margin-top:0px;margin-right:0px;margin-left:0px;\\">Backups - We keep backups but we are not responsible for data loss, you are fully responsible for all backups.<\\/li><li style=\\"margin-top:0px;margin-right:0px;margin-left:0px;\\">We Don\'t support any child porn or such material.<\\/li><li style=\\"margin-top:0px;margin-right:0px;margin-left:0px;\\">No spam-related sites or material, such as email lists, mass mail programs, and scripts, etc.<\\/li><li style=\\"margin-top:0px;margin-right:0px;margin-left:0px;\\">No harassing material that may cause people to retaliate against you.<\\/li><li style=\\"margin-top:0px;margin-right:0px;margin-left:0px;\\">No phishing pages.<\\/li><li style=\\"margin-top:0px;margin-right:0px;margin-left:0px;\\">You may not run any exploitation script from the server. reason can be terminated immediately.<\\/li><li style=\\"margin-top:0px;margin-right:0px;margin-left:0px;\\">If Anyone attempting to hack or exploit the server by using your script or hosting, we will terminate your account to keep safe other users.<\\/li><li style=\\"margin-top:0px;margin-right:0px;margin-left:0px;\\">Malicious Botnets are strictly forbidden.<\\/li><li style=\\"margin-top:0px;margin-right:0px;margin-left:0px;\\">Spam, mass mailing, or email marketing in any way are strictly forbidden here.<\\/li><li style=\\"margin-top:0px;margin-right:0px;margin-left:0px;\\">Malicious hacking materials, trojans, viruses, &amp; malicious bots running or for download are forbidden.<\\/li><li style=\\"margin-top:0px;margin-right:0px;margin-left:0px;\\">Resource and cronjob abuse is forbidden and will result in suspension or termination.<\\/li><li style=\\"margin-top:0px;margin-right:0px;margin-left:0px;\\">Php\\/CGI proxies are strictly forbidden.<\\/li><li style=\\"margin-top:0px;margin-right:0px;margin-left:0px;\\">CGI-IRC is strictly forbidden.<\\/li><li style=\\"margin-top:0px;margin-right:0px;margin-left:0px;\\">No fake or disposal mailers, mass mailing, mail bombers, SMS bombers, etc.<\\/li><li style=\\"margin-top:0px;margin-right:0px;margin-left:0px;\\">NO CREDIT OR REFUND will be granted for interruptions of service, due to User Agreement violations.<\\/li><\\/ul><\\/div><div class=\\"mb-5\\" style=\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\"><h3 class=\\"mb-3\\" style=\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\">Terms &amp; Conditions for Users<\\/h3><p class=\\"font-18\\" style=\\"margin-right:0px;margin-left:0px;font-size:18px;\\">Before getting to this site, you are consenting to be limited by these site Terms and Conditions of Use, every single appropriate law, and guidelines, and concur that you are answerable for consistency with any material neighborhood laws. If you disagree with any of these terms, you are restricted from utilizing or getting to this site.<\\/p><\\/div><div class=\\"mb-5\\" style=\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\"><h3 class=\\"mb-3\\" style=\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\">Support<\\/h3><p class=\\"font-18\\" style=\\"margin-right:0px;margin-left:0px;font-size:18px;\\">Whenever you have downloaded our item, you may get in touch with us for help through email and we will give a valiant effort to determine your issue. We will attempt to answer using the Email for more modest bug fixes, after which we will refresh the center bundle. Content help is offered to confirmed clients by Tickets as it were. Backing demands made by email and Livechat.<\\/p><p class=\\"my-3 font-18 font-weight-bold\\" style=\\"margin-right:0px;margin-left:0px;font-size:18px;\\">On the off chance that your help requires extra adjustment of the System, at that point, you have two alternatives:<\\/p><ul class=\\"font-18\\" style=\\"padding-left:15px;list-style-type:disc;font-size:18px;\\"><li style=\\"margin-top:0px;margin-right:0px;margin-left:0px;\\">Hang tight for additional update discharge.<\\/li><li style=\\"margin-top:0px;margin-right:0px;margin-left:0px;\\">Or on the other hand, enlist a specialist (We offer customization for extra charges).<\\/li><\\/ul><\\/div><div class=\\"mb-5\\" style=\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\"><h3 class=\\"mb-3\\" style=\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\">Ownership<\\/h3><p class=\\"font-18\\" style=\\"margin-right:0px;margin-left:0px;font-size:18px;\\">You may not guarantee scholarly or selective possession of any of our items, altered or unmodified. All items are property, we created them. Our items are given \\"with no guarantees\\" without guarantee of any sort, either communicated or suggested. On no occasion will our juridical individual be subject to any harms including, however not restricted to, immediate, roundabout, extraordinary, accidental, or significant harms or different misfortunes emerging out of the utilization of or powerlessness to utilize our items.<\\/p><\\/div><div class=\\"mb-5\\" style=\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\"><h3 class=\\"mb-3\\" style=\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\">Warranty<\\/h3><p class=\\"font-18\\" style=\\"margin-right:0px;margin-left:0px;font-size:18px;\\">We don\'t offer any guarantee or assurance of these Services in any way. When our Services have been modified we can\'t ensure they will work with all outsider plugins, modules, or internet browsers. Program similarity ought to be tried against the show formats on the demo worker. If you don\'t mind guarantee that the programs you use will work with the component, as we can not ensure that our systems will work with all program mixes.<\\/p><\\/div><div class=\\"mb-5\\" style=\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\"><h3 class=\\"mb-3\\" style=\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\">Unauthorized\\/Illegal Usage<\\/h3><p class=\\"font-18\\" style=\\"margin-right:0px;margin-left:0px;font-size:18px;\\">You may not utilize our things for any illicit or unapproved reason or may you, in the utilization of the stage, disregard any laws in your locale (counting yet not restricted to copyright laws) just as the laws of your nation and International law. Specifically, it is disallowed to utilize the things on our foundation for pages that advance: brutality, illegal intimidation, hard sexual entertainment, bigotry, obscenity content or warez programming joins.<br \\/><br \\/>You can\'t imitate, copy, duplicate, sell, exchange or adventure any of our segment, utilization of the offered on our things, or admittance to the administration without the express composed consent by us or item proprietor.<br \\/><br \\/>Our Members are liable for all substance posted on the discussion and demo and movement that happens under your record.<br \\/><br \\/>We hold the chance of hindering your participation account quickly if we will think about a particularly not allowed conduct.<br \\/><br \\/>If you make a record on our site, you are liable for keeping up the security of your record, and you are completely answerable for all exercises that happen under the record and some other activities taken regarding the record. You should quickly inform us, of any unapproved employments of your record or some other penetrates of security.<\\/p><\\/div><div class=\\"mb-5\\" style=\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\"><h3 class=\\"mb-3\\" style=\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\">Fiverr, Seoclerks Sellers Or Affiliates<\\/h3><p class=\\"font-18\\" style=\\"margin-right:0px;margin-left:0px;font-size:18px;\\">We do NOT ensure full SEO campaign conveyance within 24 hours. We make no assurance for conveyance time by any means. We give our best assessment to orders during the putting in of requests, anyway, these are gauges. We won\'t be considered liable for loss of assets, negative surveys or you being prohibited for late conveyance. If you are selling on a site that requires time touchy outcomes, utilize Our SEO Services at your own risk.<\\/p><\\/div><div class=\\"mb-5\\" style=\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\"><h3 class=\\"mb-3\\" style=\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\">Payment\\/Refund Policy<\\/h3><p class=\\"font-18\\" style=\\"margin-right:0px;margin-left:0px;font-size:18px;\\">No refund or cash back will be made. After a deposit has been finished, it is extremely unlikely to invert it. You should utilize your equilibrium on requests our administrations, Hosting, SEO campaign. You concur that once you complete a deposit, you won\'t document a debate or a chargeback against us in any way, shape, or form.<br \\/><br \\/>If you document a debate or chargeback against us after a deposit, we claim all authority to end every single future request, prohibit you from our site. False action, for example, utilizing unapproved or taken charge cards will prompt the end of your record. There are no special cases.<\\/p><\\/div><div class=\\"mb-5\\" style=\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\"><h3 class=\\"mb-3\\" style=\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\">Free Balance \\/ Coupon Policy<\\/h3><p class=\\"font-18\\" style=\\"margin-right:0px;margin-left:0px;font-size:18px;\\">We offer numerous approaches to get FREE Balance, Coupons and Deposit offers yet we generally reserve the privilege to audit it and deduct it from your record offset with any explanation we may it is a sort of misuse. If we choose to deduct a few or all of free Balance from your record balance, and your record balance becomes negative, at that point the record will naturally be suspended. If your record is suspended because of a negative Balance you can request to make a custom payment to settle your equilibrium to actuate your record.<\\/p><\\/div>"}',
                'created_at' => '2020-07-04 23:42:52',
                'updated_at' =>  '2022-03-30 11:23:12'
            ],
            [
                'id' => 5,
                'data_keys' => 'maintenance.data',
                'data_values' => '{"heading":"The site is under maintenance","description":"<div class=\\"mb-5\\" style=\\"color: rgb(111, 111, 111); font-family: Nunito, sans-serif; margin-bottom: 3rem !important;\\"><h3 class=\\"mb-3\\" style=\\"text-align: center; font-weight: 600; line-height: 1.3; font-size: 24px; font-family: Exo, sans-serif; color: rgb(54, 54, 54);\\">What information do we collect?<\\/h3><p class=\\"font-18\\" style=\\"text-align: center; margin-right: 0px; margin-left: 0px; font-size: 18px !important;\\">We gather data from you when you register on our site, submit a request, buy any services, react to an overview, or round out a structure. At the point when requesting any assistance or enrolling on our site, as suitable, you might be approached to enter your: name, email address, or telephone number. You may, nonetheless, visit our site anonymously.<\\/p><\\/div>"}',
                'created_at' => '2020-07-04 23:42:52',
                'updated_at' =>  '2022-03-30 11:23:12'
            ],
            [
                'id' => 6,
                'data_keys' => 'banner.content',
                'data_values' => '{"has_image":"1","tile":"Multisports Accumolator Bonous","heading":"Get up to 70% more on your returns","button_name":"Bet Now","button_url":"https:\\/\\/www.google.com\\/","background_image":"63da15572d12a1675236695.png"}',
                'created_at' => '2020-07-04 23:42:52',
                'updated_at' =>  '2022-03-30 11:23:12'
            ],
            [
                'id' => 7,
                'data_keys' => 'footer.content',
                'data_values' => '{"heading":"About Us","details":"Welcome to our sports betting platform. Explore a wide array of thrilling sports events and bet on your favorite teams to win big. Our user-friendly interface ensures a seamless experience, with secure transactions."}',
                'created_at' => '2022-08-01 06:07:22',
                'updated_at' => '2023-07-29 06:52:08',
            ],
            [
                'id' => 8,
                'data_keys' => 'footer.element',
                'data_values' => '{"title":"Master Card","has_image":"1","payment_method_image":"62e782c732a571659339463.png"}',
                'created_at' => '2022-08-01 06:07:43',
                'updated_at' => '2023-07-19 09:56:28',
            ],
            [
                'id' => 9,
                'data_keys' => 'footer.element',
                'data_values' => '{"title":"Payonner","has_image":"1","payment_method_image":"62e782da8714f1659339482.png"}',
                'created_at' => '2022-08-01 06:08:02',
                'updated_at' => '2023-07-19 09:56:28',
            ],
            [
                'id' => 10,
                'data_keys' => 'footer.element',
                'data_values' => '{"title":"Paypal","has_image":"1","payment_method_image":"62e782e86ea801659339496.png"}',
                'created_at' => '2022-08-01 06:08:16',
                'updated_at' => '2023-07-19 09:56:28',
            ],
            [
                'id' => 11,
                'data_keys' => 'footer.element',
                'data_values' => '{"title":"Visa","has_image":"1","payment_method_image":"62e784e0868b91659340000.png"}',
                'created_at' => '2022-08-01 06:16:40',
                'updated_at' => '2023-07-19 09:56:28',
            ],
            [
                'id' => 12,
                'data_keys' => 'social_icon.element',
                'data_values' => '{"title":"Facebook","icon":"<i class=\\"fab fa-facebook-f\\"><\\/i>","url":"https:\\/\\/www.facebook.com\\/"}',
                'created_at' => '2022-08-01 06:30:03',
                'updated_at' => '2023-07-19 09:56:28',
            ],
            [
                'id' => 13,
                'data_keys' => 'social_icon.element',
                'data_values' => '{"title":"Twitter","icon":"<i class=\\"fab fa-twitter\\"><\\/i>","url":"https:\\/\\/www.twitter.com\\/"}',
                'created_at' => '2022-08-01 07:15:54',
                'updated_at' => '2023-07-19 09:56:28',
            ],
            [
                'id' => 14,
                'data_keys' => 'social_icon.element',
                'data_values' => '{"title":"Linkedin","icon":"<i class=\\"fab fa-linkedin-in\\"><\\/i>","url":"https:\\/\\/www.linkedin.com\\/"}',
                'created_at' => '2022-08-01 07:16:20',
                'updated_at' => '2023-07-19 09:56:28',
            ],
            [
                'id' => 15,
                'data_keys' => 'social_icon.element',
                'data_values' => '{"title":"Instagram","icon":"<i class=\\"fab fa-instagram\\"><\\/i>","url":"https:\\/\\/www.instagram.com\\/"}',
                'created_at' => '2022-08-01 07:16:45',
                'updated_at' => '2023-07-19 09:56:28',
            ],
            [
                'id' => 16,
                'data_keys' => 'contact.content',
                'data_values' => '{"has_image":"1","heading":"Get In Touch","latitude":"40.6708314","longitude":"-73.9529734","background_image":"62e7bd71160691659354481.png"}',
                'created_at' => '2022-08-01 10:18:01',
                'updated_at' => '2023-07-19 09:56:28',
            ],
            [
                'id' => 17,
                'data_keys' => 'contact.element',
                'data_values' => '{"icon":"<i class=\\"fas fa-map-marker-alt\\"><\\/i>","heading":"Address Details","details":"1520 North Kierland Bl.100 Old City"}',
                'created_at' => '2022-08-01 10:19:05',
                'updated_at' => '2023-07-19 09:56:28',
            ],
            [
                'id' => 18,
                'data_keys' => 'contact.element',
                'data_values' => '{"icon":"<i class=\\"fas fa-phone-alt\\"><\\/i>","heading":"Contact No","details":"0123 - 4567 -890"}',
                'created_at' => '2022-08-01 10:20:06',
                'updated_at' => '2023-07-19 09:56:28',
            ],
            [
                'id' => 19,
                'data_keys' => 'contact.element',
                'data_values' => '{"icon":"<i class=\\"far fa-envelope\\"><\\/i>","heading":"Email Details","details":"support@mail.com"}',
                'created_at' => '2022-08-01 10:21:39',
                'updated_at' => '2023-07-19 09:56:28',
            ],
            [
                'id' => 20,
                'data_keys' => 'authorization.content',
                'data_values' => '{"has_image":"1","front_image":"62e7d5f2ea9351659360754.png","background_image":"62e7cc30417f41659358256.png"}',
                'created_at' => '2022-08-01 11:20:54',
                'updated_at' => '2023-07-19 09:56:28',
            ],
            [
                'id' => 21,
                'data_keys' => 'login.content',
                'data_values' => '{"has_image":"1","heading":"Login to your account","image":"63da2fb7ba5391675243447.png","background_image":"641554dacd4141679119578.png"}',
                'created_at' => '2023-02-01 09:24:07',
                'updated_at' => '2023-07-19 09:56:28',
            ],
            [
                'id' => 22,
                'data_keys' => 'maintenance.content',
                'data_values' => '{"has_image":"1","image":"63da2fc6ddb3b1675243462.png"}',
                'created_at' => '2023-02-01 09:24:22',
                'updated_at' => '2023-07-19 09:56:28',
            ],
            [
                'id' => 23,
                'data_keys' => 'register.content',
                'data_values' => '{"has_image":"1","heading":"Register your account","image":"63da2fe9646871675243497.png","background_image":"641555438a68a1679119683.png"}',
                'created_at' => '2023-02-01 09:24:57',
                'updated_at' => '2023-07-19 09:56:28',
            ],
            [
                'id' => 24,
                'data_keys' => 'user_ban.content',
                'data_values' => '{"has_image":"1","image":"63da3004222e21675243524.png","background_image":"64155c1346fd61679121427.png"}',
                'created_at' => '2023-02-01 09:25:24',
                'updated_at' => '2023-07-19 09:56:28',
            ],
            [
                'id' => 25,
                'data_keys' => 'kyc_instructions.content',
                'data_values' => '{"for_verification":"Attention, valued user! Your account\'s KYC (Know Your Customer) verification is currently pending. To continue enjoying our services and ensure a seamless betting experience, kindly complete the KYC process. Submit the required documents via our secure portal, and we\'ll expedite the verification promptly. Thank you for your cooperation!","for_pending":"To ensure the security of our platform and comply with regulations, we kindly request you to complete your KYC (Know Your Customer) verification. Your account\'s KYC verification is currently pending"}',
                'created_at' => '2023-02-01 09:43:19',
                'updated_at' => '2023-07-27 11:08:46',
            ],
            [
                'id' => 26,
                'data_keys' => 'forget_password.content',
                'data_values' => '{"has_image":"1","image":"6415596045e571679120736.png","background_image":"641559610c3811679120737.png"}',
                'created_at' => '2023-03-18 06:25:36',
                'updated_at' => '2023-07-19 09:56:28',
            ],
            [
                'id' => 27,
                'data_keys' => 'code_verify.content',
                'data_values' => '{"has_image":"1","image":"64155a67e78851679120999.png","background_image":"64155a687f0e31679121000.png"}',
                'created_at' => '2023-03-18 06:29:59',
                'updated_at' => '2023-07-19 09:56:28',
            ],
            [
                'id' => 28,
                'data_keys' => 'reset_password.content',
                'data_values' => '{"has_image":"1","image":"64155b17a468a1679121175.png","background_image":"64155b183980a1679121176.png"}',
                'created_at' => '2023-03-18 06:32:55',
                'updated_at' => '2023-07-19 09:56:28',
            ],
            [
                'id' => 29,
                'data_keys' => 'breadcrumb.content',
                'data_values' => '{"has_image":"1","image":"6416bab89c5151679211192.png"}',
                'created_at' => '2023-03-19 07:33:12',
                'updated_at' => '2023-07-19 09:56:28',
            ],
            [
                'id' => 30,
                'data_keys' => 'blog.element',
                'data_values' => '{"has_image":["1"],"title":"Phasellus viverra nulla ut metus","description":"Rerum lorem aut moll.\\u00a0<span><font color=\\"#212529\\">Donec mi odio, faucibus at, scelerisque quis, convallis in, nisi. Sed lectus. Mauris turpis nunc, blandit et, volutpat molestie, porta ut, ligula. Fusce vulputate eleifend sapien. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus.<\\/font><\\/span><div><br \\/><\\/div><div>Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; In ac dui quis mi consectetuer lacinia. Aenean vulputate eleifend tellus. Vestibulum ullamcorper mauris at ligula. Vivamus quis mi. Pellentesque commodo eros a enim.<\\/div><div><br \\/><\\/div><div>Praesent egestas tristique nibh. Nam ipsum risus, rutrum vitae, vestibulum eu, molestie vel, lacus. In consectetuer turpis ut velit. Suspendisse faucibus, nunc et pellentesque egestas, lacus ante convallis tellus, vitae iaculis lacus elit id tortor. Nulla sit amet est.<\\/div><div><br \\/><\\/div><div>Vestibulum rutrum, mi nec elementum vehicula, eros quam gravida nisl, id fringilla neque ante vel mi. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Phasellus nec sem in justo pellentesque facilisis. Praesent ac massa at ligula laoreet iaculis. Phasellus blandit leo ut odio.<\\/div><div><br \\/><\\/div><div>Nullam dictum felis eu pede mollis pretium. Ut varius tincidunt libero. Sed consequat, leo eget bibendum sodales, augue velit cursus nunc, quis gravida magna mi a libero. Mauris turpis nunc, blandit et, volutpat molestie, porta ut, ligula. Aenean ut eros et nisl sagittis vestibulum.<\\/div>","image":"64ab9a15c3cea1688967701.jpg"}',
                'created_at' => '2023-04-29 06:57:19',
                'updated_at' => '2023-07-19 09:56:29',
            ],
            [
                'id' => 31,
                'data_keys' => 'blog.element',
                'data_values' => '{"has_image":["1"],"title":"Praesent nec nisl a purus","description":"<div>Proin viverra, ligula sit amet ultrices semper, ligula arcu tristique sapien, a accumsan nisi mauris ac eros. Cras varius. Curabitur nisi. Duis arcu tortor, suscipit eget, imperdiet nec, imperdiet iaculis, ipsum. Nullam quis ante.<\\/div><div><br \\/><\\/div><div>Sed mollis, eros et ultrices tempus, mauris ipsum aliquam libero, non adipiscing dolor urna a orci. In auctor lobortis lacus. Curabitur ullamcorper ultricies nisi. Phasellus tempus. Fusce convallis metus id felis luctus adipiscing.<\\/div><div><br \\/><\\/div><div>Ut a nisl id ante tempus hendrerit. Pellentesque posuere. Etiam imperdiet imperdiet orci. Sed fringilla mauris sit amet nibh. Nunc egestas, augue at pellentesque laoreet, felis eros vehicula leo, at malesuada velit leo quis pede.<\\/div><div><br \\/><\\/div><div>Praesent egestas tristique nibh. Morbi ac felis. Phasellus blandit leo ut odio. Vestibulum ullamcorper mauris at ligula. Donec posuere vulputate arcu.<\\/div><div><br \\/><\\/div><div>Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Sed aliquam, nisi quis porttitor congue, elit erat euismod orci, ac placerat dolor lectus quis orci. Nullam accumsan lorem in dui. Aliquam eu nunc. Nullam accumsan lorem in dui. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem.<\\/div>","image":"64ab9a2dd8cc71688967725.jpg"}',
                'created_at' => '2023-04-29 06:57:38',
                'updated_at' => '2023-07-19 09:56:29',
            ],
            [
                'id' => 32,
                'data_keys' => 'blog.element',
                'data_values' => '{"has_image":["1"],"title":"Nam pretium turpis et arcu","description":"<div>Phasellus nec sem in justo pellentesque facilisis. Mauris sollicitudin fermentum libero. Ut tincidunt tincidunt erat. Vestibulum facilisis, purus nec pulvinar iaculis, ligula mi congue nunc, vitae euismod ligula urna in dolor. Vestibulum fringilla pede sit amet augue.<\\/div><div><br \\/><\\/div><div>Fusce a quam. Aenean commodo ligula eget dolor. Fusce a quam. Nullam cursus lacinia erat. Cras sagittis.<\\/div><div><br \\/><\\/div><div>Nullam quis ante. Nulla porta dolor. Pellentesque posuere. Suspendisse pulvinar, augue ac venenatis condimentum, sem libero volutpat nibh, nec pellentesque velit pede quis nunc. Suspendisse non nisl sit amet velit hendrerit rutrum.<\\/div><div><br \\/><\\/div><div>Vestibulum fringilla pede sit amet augue. Phasellus ullamcorper ipsum rutrum nunc. Cras risus ipsum, faucibus ut, ullamcorper id, varius ac, leo. Sed hendrerit. Aenean viverra rhoncus pede.<\\/div><div><br \\/><\\/div><div>Curabitur turpis. Nunc sed turpis. Phasellus volutpat, metus eget egestas mollis, lacus lacus blandit dui, id egestas quam mauris ut lacus. Praesent ac massa at ligula laoreet iaculis. Phasellus nec sem in justo pellentesque facilisis.<\\/div>","image":"64ab9a38dbd391688967736.jpg"}',
                'created_at' => '2023-04-29 06:57:54',
                'updated_at' => '2023-07-19 09:56:29',
            ],
            [
                'id' => 33,
                'data_keys' => 'blog.element',
                'data_values' => '{"has_image":["1"],"title":"In ac felis quis tortor","description":"<div>Proin viverra, ligula sit amet ultrices semper, ligula arcu tristique sapien, a accumsan nisi mauris ac eros. Phasellus magna. Morbi mattis ullamcorper velit. Phasellus volutpat, metus eget egestas mollis, lacus lacus blandit dui, id egestas quam mauris ut lacus. Curabitur ligula sapien, tincidunt non, euismod vitae, posuere imperdiet, leo.<\\/div><div><br \\/><\\/div><div>Etiam vitae tortor. Nunc nec neque. Curabitur blandit mollis lacus. Proin sapien ipsum, porta a, auctor quis, euismod ut, mi. Quisque malesuada placerat nisl.<\\/div><div><br \\/><\\/div><div>Pellentesque auctor neque nec urna. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Donec sodales sagittis magna.. Proin viverra, ligula sit amet ultrices semper, ligula arcu tristique sapien, a accumsan nisi mauris ac eros.<\\/div><div><br \\/><\\/div><div>Pellentesque posuere. Vivamus elementum semper nisi. Suspendisse feugiat. Etiam sollicitudin, ipsum eu pulvinar rutrum, tellus ipsum laoreet sapien, quis venenatis ante odio sit amet eros. Proin faucibus arcu quis ante.<\\/div><div><br \\/><\\/div><div>Aenean vulputate eleifend tellus. Morbi mattis ullamcorper velit. Vestibulum volutpat pretium libero. Vestibulum dapibus nunc ac augue. Ut varius tincidunt libero.<\\/div>","image":"64ab9a420a8631688967746.jpg"}',
                'created_at' => '2023-04-29 07:07:22',
                'updated_at' => '2023-07-19 09:56:29',
            ],
            [
                'id' => 34,
                'data_keys' => 'blog.element',
                'data_values' => '{"has_image":["1"],"title":"Donec orci lectus aliquam ut","description":"<div>Suspendisse nisl elit, rhoncus eget, elementum ac, condimentum eget, diam. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Nullam cursus lacinia erat. Phasellus a est. Praesent venenatis metus at tortor pulvinar varius.<\\/div><div><br \\/><\\/div><div>Praesent egestas tristique nibh. Vestibulum facilisis, purus nec pulvinar iaculis, ligula mi congue nunc, vitae euismod ligula urna in dolor. Proin magna. Cras id dui. Phasellus blandit leo ut odio.<\\/div><div><br \\/><\\/div><div>Nam ipsum risus, rutrum vitae, vestibulum eu, molestie vel, lacus. Fusce egestas elit eget lorem. Vestibulum turpis sem, aliquet eget, lobortis pellentesque, rutrum eu, nisl. Praesent egestas neque eu enim. Nunc nulla.<\\/div><div><br \\/><\\/div><div>Pellentesque ut neque. Morbi nec metus. Maecenas malesuada. Vestibulum ullamcorper mauris at ligula. Nam ipsum risus, rutrum vitae, vestibulum eu, molestie vel, lacus.<\\/div><div><br \\/><\\/div><div>Pellentesque libero tortor, tincidunt et, tincidunt eget, semper nec, quam. Praesent metus tellus, elementum eu, semper a, adipiscing nec, purus. Sed aliquam ultrices mauris. Nullam sagittis. Nullam tincidunt adipiscing enim.<\\/div>","image":"64ab9caa6fc861688968362.jpg"}',
                'created_at' => '2023-04-29 07:07:39',
                'updated_at' => '2023-07-19 09:56:29',
            ],
            [
                'id' => 36,
                'data_keys' => 'blog.element',
                'data_values' => '{"heading":"Latest News","subheading":"Vestibulum suscipit nulla quis orci. Etiam vitae tortor."}',
                'created_at' => '2023-04-29 07:07:39',
                'updated_at' => '2023-07-19 09:56:29',
            ],
            [
                'id' => 37,
                'data_keys' => 'banner.element',
                'data_values' => '{"has_image":"1","image":"64a557065f66a1688557318.jpg"}',
                'created_at' => '2023-05-09 06:40:42',
                'updated_at' => '2023-07-19 09:56:29',
            ],
            [
                'id' => 38,
                'data_keys' => 'banner.element',
                'data_values' => '{"has_image":"1","image":"64a5572a3e7991688557354.jpg"}',
                'created_at' => '2023-05-09 06:42:14',
                'updated_at' => '2023-07-19 09:56:29',
            ],
            [
                'id' => 39,
                'data_keys' => 'banner.element',
                'data_values' => '{"has_image":"1","image":"64a55730164b11688557360.jpg"}',
                'created_at' => '2023-07-05 11:42:40',
                'updated_at' => '2023-07-19 09:56:29',
            ],
            [
                'id' => 40,
                'data_keys' => 'policy_pages.element',
                'data_values' => '{"title":"Refund Policy","details":"<p style=\\"margin-right:0px;margin-bottom:24px;margin-left:0px;\\">Sure, here is the refund policy for a sports betting platform in terms of sports betting:<\\/p><p style=\\"margin:24px 0px;\\"><b><font size=\\"4\\">Refund Policy<\\/font><\\/b><\\/p><p style=\\"margin:24px 0px;\\"><b>1. Purpose<\\/b><\\/p><p style=\\"margin:24px 0px;\\">This refund policy outlines the circumstances under which our sports betting platform will issue a refund to a customer.<\\/p><p style=\\"margin:24px 0px;\\"><b>2. Eligibility<\\/b><\\/p><p style=\\"margin:24px 0px;\\">Only customers who have placed a bet on a sporting event on our platform and have not yet received any winnings are eligible for a refund.<\\/p><p style=\\"margin:24px 0px;\\"><b>3. Reasons for Refund<\\/b><\\/p><p style=\\"margin:24px 0px;\\">Refunds may be issued for the following reasons:<\\/p><ul style=\\"margin-top:4px;margin-bottom:4px;\\"><li style=\\"margin-bottom:10px;\\">Accidental bet:\\u00a0If a customer accidentally places a bet on a sporting event, they may request a refund for the amount bet.<\\/li><li style=\\"margin-bottom:10px;\\">Fraudulent activity:\\u00a0If a customer\'s account is compromised and fraudulent activity occurs, they may request a refund for any losses incurred.<\\/li><li style=\\"margin-bottom:10px;\\">Technical error:\\u00a0If a technical error on our part causes a customer to lose money, they may request a refund for the amount lost.<\\/li><li style=\\"margin-bottom:10px;\\">Canceled event:\\u00a0If a sporting event is canceled, customers may be eligible for a refund, depending on the circumstances.<\\/li><\\/ul><p style=\\"margin:24px 0px;\\"><b>4. Process for Requesting a Refund<\\/b><\\/p><p style=\\"margin:24px 0px;\\">To request a refund, customers must contact our customer support team and provide the following information:<\\/p><ul style=\\"margin-top:4px;margin-bottom:4px;\\"><li style=\\"margin-bottom:10px;\\">Their name<\\/li><li style=\\"margin-bottom:10px;\\">Their account number<\\/li><li style=\\"margin-bottom:10px;\\">The reason for the refund<\\/li><li style=\\"margin-bottom:10px;\\">Any supporting documentation<\\/li><\\/ul><p style=\\"margin:24px 0px;\\"><b>5. Decision<\\/b><\\/p><p style=\\"margin:24px 0px;\\">Our customer support team will review the refund request and make a decision within 10 business days. If the request is approved, the refund will be processed within 30 business days.<\\/p><p style=\\"margin:24px 0px;\\"><b>6. Limitations<\\/b><\\/p><p style=\\"margin:24px 0px;\\">We reserve the right to deny any refund request if we believe that the request is fraudulent or otherwise invalid. We also reserve the right to change or modify this refund policy at any time.<\\/p><p style=\\"margin:24px 0px;\\"><b>7. Contact Information<\\/b><\\/p><p style=\\"margin:24px 0px;\\">If you have any questions about our refund policy, please contact our customer support team at [email protected]<\\/p><p style=\\"margin:24px 0px;\\">Additional Notes<\\/p><ul style=\\"margin-top:4px;margin-bottom:4px;\\"><li style=\\"margin-bottom:10px;\\">Refunds will only be issued for bets that have not yet been settled.<\\/li><li style=\\"margin-bottom:10px;\\">Refunds will not be issued for bets that have already been won or lost.<\\/li><li style=\\"margin-bottom:10px;\\">Refunds may be subject to a processing fee.<\\/li><\\/ul><p style=\\"margin:24px 0px;\\">We hope this clarifies our refund policy. If you have any further questions, please do not hesitate to contact us.<\\/p>"}',
                'created_at' => '2023-07-29 07:09:09',
                'updated_at' => '2023-07-29 07:11:38',
            ],
            [
                'id' => 41,
                'data_keys' => 'aff_register.content',
                'data_values' => '{"has_image":"1","heading":"Register as Affiliate","image":"63da2fe9646871675243497.png","background_image":"641555438a68a1679119683.png"}',
                'created_at' => '2023-02-01 09:24:57',
                'updated_at' => '2023-07-19 09:56:28',
            ],
        ]);
    }
}
