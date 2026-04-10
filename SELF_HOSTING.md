# Self-Hosting Lume On Your PC

This project can be hosted directly from your own computer for local use, LAN sharing, or temporary demos.

## Quick Start

1. Make sure MySQL is running.
2. Make sure your `.env` has the correct database values.
3. Start the app:

```bash
composer run serve:self
```

By default, this starts the app on:

```text
http://0.0.0.0:8080
```

On the same PC, open:

```text
http://localhost:8080
```

## Share On Your Local Network

To open the site from another device on the same Wi-Fi/LAN:

1. Find your PC's local IP address.
2. Open from another device using:

```text
http://YOUR-LAN-IP:8080
```

Example:

```text
http://192.168.1.23:8080
```

## Optional: Change Host Or Port

You can change the bind address and port:

```bash
LUME_HOST=0.0.0.0 LUME_PORT=9090 composer run serve:self
```

## Important `.env` Note

If you access the site from another device, update:

```env
app.baseURL='http://YOUR-LAN-IP:8080/'
```

Example:

```env
app.baseURL='http://192.168.1.23:8080/'
```

If you only use the site on your own PC, you can keep:

```env
app.baseURL='http://localhost:8080/'
```

## Database Setup

If migrations are not yet applied:

```bash
php spark migrate
```

## Public Internet Access

If you want people outside your home network to access the site, you need one of:

- port forwarding on your router
- a tunnel like Cloudflare Tunnel or ngrok

Do not expose it publicly without understanding the security risks.

## Recommended Use

Good for:

- development
- demos
- testing on your phone/laptop

Not recommended for:

- long-term production
- sensitive public deployment
- high traffic
