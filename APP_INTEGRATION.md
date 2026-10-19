# Connect the radio player to an app or website

The PHP player can be opened directly by an app after it is deployed to a PHP-capable HTTPS web host. Use the app-friendly entry point:

```text
https://YOUR-HOST/YOUR-RADIO-PATH/app-player.php?audience=kids
https://YOUR-HOST/YOUR-RADIO-PATH/app-player.php?audience=adult
```

The audience query is a convenience filter when **Settings → Audience Mode** is set to **Flexible**. For the child-facing app, set the Kids deployment's Audience Mode to **Kids app** so the mode cannot be changed by a query string. Keep the Kids instance to stations reviewed by an adult.

## Android WebView

Add internet access to `AndroidManifest.xml`:

```xml
<uses-permission android:name="android.permission.INTERNET" />
```

Then load the deployed player page in the app's WebView. PawTunes uses JavaScript for playback controls:

```kotlin
webView.settings.javaScriptEnabled = true
webView.loadUrl("https://YOUR-HOST/YOUR-RADIO-PATH/app-player.php?audience=kids")
```

For the adult app, use `audience=adult`. Use your actual HTTPS host and radio folder in place of the placeholders. Do not point an HTTPS app at an HTTP-only stream; Android and browsers may block mixed content.

## Website iframe

```html
<iframe
  src="https://YOUR-HOST/YOUR-RADIO-PATH/app-player.php?audience=kids"
  title="JGJG Books Radio"
  width="100%"
  height="620"
  loading="lazy"
  allow="autoplay"
  style="border:0"
></iframe>
```

The app and website need the deployed player URL. A GitHub repository stores this code but does not run its PHP pages.
