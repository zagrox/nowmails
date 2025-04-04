# NowMails

A powerful email marketing solution integrated with ElasticEmail API v2 for WordPress.

## Features

- Email subscription form with shortcode support
- Integration with ElasticEmail API v2
- Subaccount management
- Email template management
- Webhook support for email events
- Analytics dashboard
- Customizable branding options

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- ElasticEmail API key

## Installation

1. Download the plugin zip file
2. Go to WordPress admin > Plugins > Add New
3. Click "Upload Plugin" and select the downloaded zip file
4. Click "Install Now" and then "Activate"

## Configuration

1. Go to WordPress admin > NowMails > Settings
2. Enter your ElasticEmail API key
3. Configure other settings as needed

## Usage

### Subscription Form

Use the shortcode `[nowmails_subscribe]` to display the subscription form on any page or post.

### API Integration

The plugin automatically integrates with ElasticEmail API v2. Make sure to:

1. Get your API key from ElasticEmail
2. Enter it in the plugin settings
3. Configure webhook settings if needed

## Development

### Directory Structure

```
nowmails/
├── assets/
│   ├── css/
│   └── js/
├── includes/
│   ├── admin/
│   ├── api/
│   └── frontend/
└── nowmails.php
```

### Building Assets

1. Install dependencies:
   ```bash
   npm install
   ```

2. Build assets:
   ```bash
   npm run build
   ```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## Support

For support, please open an issue in the GitHub repository or contact us at support@nowmails.com. 