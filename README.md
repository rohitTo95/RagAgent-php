# RAG Agent PHP - Laravel AI-Powered Chat Application

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![AI](https://img.shields.io/badge/AI-Powered-blue?style=for-the-badge)

A powerful Laravel-based Retrieval-Augmented Generation (RAG) application that combines document understanding with AI-powered conversations. This application allows users to upload documents, process them through vector embeddings, and have intelligent conversations based on the uploaded content.

## 🚀 Features

### Core Functionality
- **Document Upload & Processing**: Upload various document formats and automatically process them into searchable vector embeddings
- **AI-Powered Chat**: Engage in intelligent conversations powered by Google Gemini AI
- **RAG Implementation**: Retrieval-Augmented Generation for context-aware responses based on uploaded documents
- **Real-time Streaming**: Server-Sent Events (SSE) for real-time AI response streaming
- **Chat History**: Persistent chat history with thread management
- **Vector Search**: Advanced similarity search using Pinecone vector database

### Technical Features
- **Modern Laravel Framework**: Built on Laravel 12 with PHP 8.2+
- **Modular Architecture**: Clean, maintainable code structure
- **Vector Database Integration**: Pinecone integration for efficient similarity search
- **Multiple AI Providers**: Extensible AI provider system (currently supports Gemini)
- **Document Management**: Complete document lifecycle management
- **Responsive UI**: Modern web interface with Bootstrap Icons

## 🛠️ Technology Stack

- **Backend**: Laravel 12, PHP 8.2+
- **AI/ML**: NeuronAI Core, Google Gemini API
- **Vector Database**: Pinecone
- **Frontend**: Blade Templates, Bootstrap, JavaScript
- **Database**: SQLite (configurable)
- **Real-time**: Server-Sent Events (SSE)
- **HTTP Client**: Guzzle HTTP

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js and NPM
- A Pinecone account and API key
- Google Gemini API key

## 🔧 Installation

1. **Clone the repository**
   ```bash
   git clone <your-repository-url>
   cd RagAgent-php
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure Environment Variables**
   Edit your `.env` file with the following required variables:
   ```env
   # Database
   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite
   
   # Gemini AI Configuration
   GEMINI_API_KEY=your_gemini_api_key_here
   
   # Pinecone Configuration
   PINECONE_API_KEY=your_pinecone_api_key_here
   PINECONE_INDEX_URL=https://your-index-url.pinecone.io
   
   # Optional: Application Performance Monitoring
   INSPECTOR_API_KEY=your_inspector_api_key_here
   ```

6. **Run Database Migrations**
   ```bash
   php artisan migrate
   ```

7. **Build Frontend Assets**
   ```bash
   npm run build
   ```

8. **Start the Development Server**
   ```bash
   php artisan serve
   ```

## 🚀 Quick Start

1. **Access the Application**: Navigate to `http://localhost:8000` in your browser

2. **Upload Documents**: Use the document upload feature to add your content:
   - Supported formats: PDF, TXT, DOCX, and more
   - Documents are automatically processed and stored as vector embeddings

3. **Start Chatting**: Begin conversations with the AI:
   - Ask questions about your uploaded documents
   - Get contextual responses based on document content
   - Enjoy real-time streaming responses

4. **Manage Conversations**: Use the chat history feature to:
   - View previous conversations
   - Continue existing threads
   - Organize your chat sessions

## 📁 Project Structure

```
app/
├── Http/Controllers/       # Laravel controllers
│   ├── ChatController.php  # Chat functionality
│   └── DocumentController.php # Document management
├── Models/                 # Eloquent models
│   ├── ChatHistory.php     # Chat history model
│   ├── Document.php        # Document model
│   └── User.php           # User model
├── Neuron/                # AI integration
│   └── MyChatBot.php      # RAG chatbot implementation
└── Providers/
    └── AppServiceProvider.php

config/                    # Laravel configuration files
database/
├── migrations/           # Database schema migrations
└── database.sqlite      # SQLite database file

resources/
├── views/               # Blade templates
│   ├── chat.blade.php   # Main chat interface
│   └── welcome.blade.php # Welcome page
├── css/                 # Stylesheets
└── js/                  # JavaScript files

routes/
└── web.php              # Web routes definition

vendor/
└── neuron-core/neuron-ai/ # NeuronAI package
```

## 🔧 Configuration

### Pinecone Setup
1. Create a Pinecone account at [pinecone.io](https://pinecone.io)
2. Create a new index with appropriate dimensions (match your embedding model)
3. Get your API key and index URL
4. Update your `.env` file with the Pinecone configuration

### Gemini API Setup
1. Visit [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Generate an API key
3. Add the key to your `.env` file

### Database Configuration
The application uses SQLite by default, but you can configure other databases:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rag_agent
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## 🐛 Known Issues & Fixes

### Pinecone VectorStore Fix
**Issue**: The current version of NeuronAI has a bug in the Pinecone VectorStore implementation.

**Location**: `/vendor/neuron-core/neuron-ai/src/RAG/VectorStore/PineconeVectorStore.php`

**Fixes Applied**:
1. **Namespace Default Value**: Changed from `"__default__"` to `""` (empty string)
2. **Filter Condition**: Added proper empty check for filters in `similaritySearch()` method

**Fixed Code**:
```php
// Constructor - line 27
protected string $namespace = '' // Fixed: empty string instead of "__default__"

// similaritySearch method - proper filter handling
public function similaritySearch(array $embedding): iterable
{
    $queryParams = [
        'namespace' => $this->namespace,
        'includeMetadata' => true,
        'includeValues' => true,
        'vector' => $embedding,
        'topK' => $this->topK,
    ];

    // Only include filter parameter if filters are not empty
    if (!empty($this->filters)) {
        $queryParams['filter'] = $this->filters;
    }
    
    // ... rest of the method
}
```

## 📚 API Endpoints

### Chat Endpoints
- `POST /chat` - Send a chat message and receive AI response
- `GET /chat/history` - Retrieve chat history
- `GET /chat/history/{threadId}` - Load specific conversation thread

### Document Endpoints
- `POST /upload-document` - Upload and process documents
- `GET /documents` - List uploaded documents

## 🔧 Development

### Running Tests
```bash
php artisan test
```

### Code Style
The project uses Laravel Pint for code formatting:
```bash
./vendor/bin/pint
```

### Debugging
Enable debug mode in your `.env` file:
```env
APP_DEBUG=true
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Laravel Framework** - For the robust PHP framework
- **NeuronAI Core** - For the AI integration capabilities
- **Pinecone** - For vector database services
- **Google Gemini** - For AI language model capabilities
- **Bootstrap** - For UI components and styling

## 📞 Support

For support and questions:
- Open an issue on GitHub
- Check the documentation
- Review the code examples

## 🔄 Version History

### Current Version
- **Framework**: Laravel 12
- **PHP**: 8.2+
- **NeuronAI**: ^2.8
- **Features**: Document upload, RAG chat, vector search, real-time streaming

---

**Built with ❤️ using Laravel and AI**

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
