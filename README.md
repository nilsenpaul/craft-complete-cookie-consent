# Complete Cookie Consent plugin for Craft CMS 3.x

This plugin will help you comply with [EU Cookie law](https://www.privacypolicies.com/blog/eu-cookie-law/). As that law states, all European visitors of your site should not only be made aware of, but also consent to the cookies your site sets. This plugin will help you obtain consent for one or more cookie groups.

## Requirements

This plugin requires Craft CMS 3.0.0 or later.

**Installation**

1. Install with Composer via `composer require nilsenpaul/craft-complete-cookie-consent` from your project directory
2. Install plugin in the Craft Control Panel under Settings > Plugins
3. Alter the plugin's settings to cater to your site's needs

You can also install Complete Cookie Consent via the **Plugin Store** in the Craft Control Panel.

## Setup
Using the plugin's settings page, you have total control over the appearance and functionality of your cookie consent banner:

  * Activate and de-activate the banner from the plugin settings page
  * Only show the banner for logged in admins, for testing purposes
  * Change the colors of the banner and banner button
  * Change the text of the banner title, banner text and button text
  * Include the plugin's CSS to give your banner a base style, or use your own CSS
  * Choose a position for the banner: top, right, bottom, left or center
  * Change the name and expiration time for the preferences cookie
  * Change cookie types, make cookie types required or checked by default
  * Add geolocation to your cookie consent process by adding a [ipApi](https://ipapi.com/) API key: only show the banner to EU visitors
  
![Banner screenshot](resources/img/settings-cookietypes.png)

## Usage

After you've installed the plugin, and activated the cookie consent banner, the banner will be shown on your site. Depending on your setup, an external geolocation API will be used to determine if a cookie banner is needed. 

![Banner screenshot](resources/img/cookie-banner.png)

### Honoring your visitor's cookie preferences

Cookie consent wouldn't be of much use if you wouldn't act upon the preferences of your users. That's why the `ccc` JavaScript object is set, containing the preferences of the current visitor:

    console.log(ccc.consentSubmitted)
    # prints true or false
    
    console.log(ccc.consent.[cookieTypeHandle])
    # prints true or false, depending on your visitor's choice
    
You can use these values to load (or prevent loading of) whatever you want.

### Thank you!

  * [NYStudio107](https://github.com/nystudio107), for all the work you put in your plugins and the blog posts you write. Writing plugins is easier with your code as guidance.

### TO-DO
 * More JS examples, for example how to prevend Google Tag Manager from loading if consent is not given
 * Simple statistics, to know how the cookie banner effects your site
 * Make the plugin work with static page caching
 
### Known issues
 * The plugin does not work with static page caching (yet).
