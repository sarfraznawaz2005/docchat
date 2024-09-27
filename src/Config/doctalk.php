<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable DocTalk
    |--------------------------------------------------------------------------
    |
    | This option controls whether DocTalk is enabled in the application.
    |
    */
    'enabled' => env('DOCTALK_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | DocTalk Chat URL Path
    |--------------------------------------------------------------------------
    |
    | This is the URL path where the DocTalk chat interface will be accessible.
    | You can customize the path if necessary.
    |
    */
    'path' => 'chat-with-docs',

    /*
    |--------------------------------------------------------------------------
    | Middleware Configuration
    |--------------------------------------------------------------------------
    |
    | Specify middleware for authentication or any additional protection layers
    | for accessing the chat. You can provide multiple middleware using an array.
    |
    | Example:
    |   'middleware' => ['auth', 'another_middleware']
    |
    */
    'chat_middleware' => null, // Middleware for chat interface
    'admin_middleware' => null, // Middleware for admin interface

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | This defines which database connection should be used for DocTalk.
    | You can set this to the appropriate database driver.
    |
    */
    'db_connection' => 'pgsql',

    /*
    |--------------------------------------------------------------------------
    | Language Model (LLM) Configuration
    |--------------------------------------------------------------------------
    |
    | Define the settings for the language model provider (LLM). You can choose
    | between 'gemini' or 'openai' as the provider. Make sure to set the API key
    | and model accordingly.
    |
    */
    'llm' => [
        // Supported: 'gemini' or 'openai'
        'llm_provider' => 'gemini',
        // API key for the selected LLM provider
        'api_key' => env('DOCTALK_LLM_API_KEY'),
        // Gemini: "gemini-1.5-flash", "gemini-1.5-pro", OpenAI: "gpt-4o-mini", "gpt-4o", etc
        'llm_model' => 'gemini-1.5-flash-002',
        'options' => [
            'maxOutputTokens' => 8192, // Gemini: 8192, OpenAI: 4096 (use "max_tokens" for OpenAI)
            'temperature' => 0.5, // Gemini default: 1.0, OpenAI default: 0.7
        ],
        'generate_conversation_titles' => true, // Automatically generate conversation titles using AI
        'enable_related_questions' => true, // Enable AI-generated related questions
        'show_sources' => true, // Show source documents and page numbers under responses
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    |
    | Define the maximum upload size allowed for files during a chat session.
    | Specify whether users are allowed to upload files.
    |
    */
    'max_files_upload_size' => 25600, // Max upload size in KB (25 MB default)
    'allow_user_upload' => true, // Allow users to upload files in chat interface

    /*
    |--------------------------------------------------------------------------
    | Animated Messages
    |--------------------------------------------------------------------------
    |
    | Enable this option if you want user messages to be animated while being sent.
    |
    */
    'animated_message' => true, // Animate user messages during submission
];
