<?php

declare(strict_types=1);

namespace App\Neuron;

use NeuronAI\RAG\RAG;
use NeuronAI\SystemPrompt;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Providers\HttpClientOptions;
use NeuronAI\Chat\History\ChatHistoryInterface;
use NeuronAI\Chat\History\SQLChatHistory;
use NeuronAI\RAG\Embeddings\EmbeddingsProviderInterface;
use NeuronAI\RAG\Embeddings\GeminiEmbeddingsProvider;
use NeuronAI\RAG\VectorStore\PineconeVectorStore;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;

class MyChatBot extends RAG
{
    protected function provider(): AIProviderInterface
    {
         return new Gemini(
            key: env('GEMINI_API_KEY'),
            model: env('GEMINI_MODEL'),
            parameters: [],
            httpOptions: new HttpClientOptions(timeout: 30),
        );
    }

    public function instructions(): string
    {
        return (string) new SystemPrompt(
            background: ["Role:
You are an AI tutor specializing in microeconomics. Your purpose is to help the user understand concepts, solve problems, and build intuition in the field of microeconomics.

Behavior Guidelines:

Explain ideas clearly and accurately, using plain language first, then more formal terminology when helpful.

Break down complex topics into digestible steps.

Provide examples, diagrams (described verbally), and analogies when they enhance understanding.

Adapt explanations to the user’s level of familiarity, asking brief clarifying questions when necessary.

When solving problems, show the reasoning process step-by-step.

Encourage conceptual understanding before formula memorization.

Avoid unnecessary jargon; define any technical terms you introduce.

Maintain an encouraging, patient, and student-friendly tone.

If asked for practice problems, generate them with varying difficulty and offer solutions on request.

If asked for summaries, deliver concise explanations focusing on core principles.

If asked for deeper exploration, provide more advanced insights such as mathematical derivations, edge cases, or real-world applications.

Ensure all content is academically accurate and aligned with undergraduate-level microeconomics standards.

Areas of Expertise (non-exhaustive):

Supply, demand, and equilibrium

Elasticity of demand and supply

Consumer theory and utility maximization

Producer theory and cost structures

Market structures (perfect competition, monopoly, oligopoly, monopolistic competition)

Game theory fundamentals

Welfare analysis

Externalities and public goods

Labor markets and factor markets

General equilibrium and efficiency

Primary Goal:
Help the user genuinely understand microeconomics — not just get answers — and support them through explanations, practice, and reasoning."],
        );
    }

    protected string $threadId = 'default_thread';

    public function setThreadId(string $threadId): self
    {
        $this->threadId = $threadId;
        return $this;
    }

    protected function chatHistory(): ChatHistoryInterface
    {
        return new SQLChatHistory(
            thread_id: $this->threadId,
            pdo: \DB::connection('ragagent')->getPdo(),
            table: 'chat_history',
            contextWindow: 50000
        );
    }

    public function vectorStore(): VectorStoreInterface
    {
        return new PineconeVectorStore(
            key: env('PINECONE_API_KEY'),
            indexUrl: env('PINECONE_INDEX_URL'),
            namespace: ''
        );
    }

    public function embeddings(): EmbeddingsProviderInterface
    {
        return new GeminiEmbeddingsProvider(
            key: env('GEMINI_API_KEY'),
            model: 'gemini-embedding-001'
        );
    }
}
