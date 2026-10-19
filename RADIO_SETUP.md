# JGJG Books Radio setup

This is a self-hosted PHP radio player based on PawTunes. GitHub stores its source; a PHP-capable HTTPS web host must run it. The player connects listeners directly to each configured station's stream.

## Add station streams

1. Deploy the project to a PHP 7.4+ web host with the required extensions listed in `README.md`.
2. Open `/panel/` and change the initial administrator password before making the panel public.
3. Open **Channels → Add Channel**. Enter the station name, select an audience, and paste the station's direct stream or mountpoint URL under **Streams (Audio)**. Use MP3 where available and HTTPS where the station provides it.
4. Save, then test from the target website and an Android device. Some streams block embedding or only support HTTP; those may not play inside an HTTPS site or Android WebView.

A station page URL is not necessarily a stream URL. Use the direct link supplied by the station or its SHOUTcast player. The directory can list stations, but it does not guarantee each URL stays online or works in every browser.

The player can handle ordinary internet radio too, provided the station offers a browser-compatible direct audio stream (commonly MP3 or AAC). For a starting catalog, see [Recommended Radio Streams](https://github.com/deroverda/recommended-radio-streams). It publishes per-genre `.m3u` playlists, but PawTunes expects each channel's direct audio URL, so copy individual stream URLs from a playlist rather than adding the playlist URL itself. List data is CC0; that does not establish the music's licensing or a station's permission to be embedded.

## Kids and Adult players

Each channel has an audience label:

- **Kids** appears in the Kids player.
- **All ages** appears in Kids and Adult players.
- **Adults** is hidden from the Kids player.

For a child-facing APK, deploy a separate Kids instance and set **Settings → Audience Mode** to **Kids app**. That fixed setting cannot be changed with a URL parameter. Use a separate Adult instance set to **Adult app**. For a single flexible website instance, select **Flexible** and use `?audience=kids` to preview the Kids channel filter. The flexible query-string view is for convenience, not a parental access-control system.

The SHOUTcast Directory states that it does not pre-screen or rate stations. Do not expose its unreviewed search results in the Kids player; mark only adult-reviewed stations as **Kids** or **All ages**.

## SHOUTcast Directory search

SHOUTcast publishes an official API for station lookup. Its API terms require using the approved API and may require a registered Developer ID. They limit use of returned information to playback, prohibit recording, and restrict storing directory data. Do not scrape the website or copy its station database. This repository currently accepts stream URLs entered in the admin panel; directory search can be connected after the account has access to the API and its requirements have been checked.

- [SHOUTcast API information](https://directory.shoutcast.com/Developer)
- [SHOUTcast API license](https://www.shoutcast.com/legal/agreements/api)
- [SHOUTcast Directory terms](https://directory.shoutcast.com/TermsOfUse)

SHOUTcast's terms also state that station broadcasters are responsible for music rights and royalties. Check the station's own terms before adding it.

## Embed in the website and Android apps

Embed the deployed HTTPS player page in the website. An Android app can open that page in a WebView or launch it in a browser; native playback requires a separate Android player implementation. Use the Kids instance URL in the Kids app and the Adult instance URL in the Adult app.

## Upstream and license

This project is modified from [PawTunes](https://github.com/Jackysi/PawTunes), licensed under the Mozilla Public License 2.0. The upstream license is included in `LICENSE`; retain its notices and provide source for modifications as required by the license.
