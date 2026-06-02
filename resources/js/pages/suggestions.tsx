import { Form, Head } from '@inertiajs/react';
import {
    BookOpenCheck,
    Lightbulb,
    MessageCircleQuestion,
    Plus,
    Trash2,
    X,
} from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type KnowledgeTopic = {
    id: number;
    topic: string;
};

type Suggestion = {
    id: number;
    question: string;
    chat_id: string | null;
    added_to_kb: boolean;
    created_at: string;
};

type Props = {
    suggestions: Suggestion[];
    topics: KnowledgeTopic[];
};

const baseUrl = '/suggestions/manage';

function timeAgo(dateStr: string): string {
    const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
    if (diff < 60) return `${diff}s ago`;
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    return `${Math.floor(diff / 86400)}d ago`;
}

function PromotePanel({
    suggestion,
    topics,
    onClose,
}: {
    suggestion: Suggestion;
    topics: KnowledgeTopic[];
    onClose: () => void;
}) {
    return (
        <div className="mt-4 rounded-lg border border-sidebar-border/70 bg-muted/30 p-4 dark:border-sidebar-border">
            <div className="mb-3 flex items-center justify-between">
                <span className="text-sm font-semibold">Add to knowledge base</span>
                <button
                    type="button"
                    onClick={onClose}
                    className="text-muted-foreground hover:text-foreground"
                >
                    <X className="size-4" />
                </button>
            </div>

            <Form
                action={`${baseUrl}/${suggestion.id}/promote`}
                method="post"
                options={{ preserveScroll: true }}
                className="grid gap-3"
            >
                {({ errors, processing }) => (
                    <>
                        <div className="grid gap-3 md:grid-cols-2">
                            <div className="grid gap-1.5">
                                <Label htmlFor={`topic-${suggestion.id}`}>Parent topic</Label>
                                {topics.length === 0 ? (
                                    <p className="text-xs text-muted-foreground">
                                        No topics yet — create one in the Knowledge base page first.
                                    </p>
                                ) : (
                                    <select
                                        id={`topic-${suggestion.id}`}
                                        name="knowledge_topic_id"
                                        required
                                        className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                    >
                                        {topics.map((t) => (
                                            <option key={t.id} value={t.id}>
                                                {t.topic}
                                            </option>
                                        ))}
                                    </select>
                                )}
                                <InputError message={errors.knowledge_topic_id} />
                            </div>

                            <div className="grid gap-1.5">
                                <Label htmlFor={`subtopic-${suggestion.id}`}>Subtopic</Label>
                                <Input
                                    id={`subtopic-${suggestion.id}`}
                                    name="sub_topic"
                                    defaultValue={suggestion.question}
                                    required
                                />
                                <InputError message={errors.sub_topic} />
                            </div>
                        </div>

                        <div className="grid gap-1.5">
                            <Label htmlFor={`desc-${suggestion.id}`}>Answer / Description</Label>
                            <textarea
                                id={`desc-${suggestion.id}`}
                                name="description"
                                required
                                rows={3}
                                placeholder="Type the answer customers should receive..."
                                className="min-h-20 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none transition-[color,box-shadow] placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50"
                            />
                            <InputError message={errors.description} />
                        </div>

                        <div className="grid gap-1.5">
                            <Label htmlFor={`kw-${suggestion.id}`}>
                                Keywords{' '}
                                <span className="font-normal text-muted-foreground">(comma separated)</span>
                            </Label>
                            <Input
                                id={`kw-${suggestion.id}`}
                                name="keywords"
                                placeholder="pricing, cost, fee"
                            />
                            <InputError message={errors.keywords} />
                        </div>

                        <div className="flex gap-2">
                            <Button size="sm" disabled={processing || topics.length === 0}>
                                <BookOpenCheck className="size-3.5" />
                                Save to knowledge base
                            </Button>
                            <Button type="button" size="sm" variant="ghost" onClick={onClose}>
                                Cancel
                            </Button>
                        </div>
                    </>
                )}
            </Form>
        </div>
    );
}

function SuggestionCard({
    suggestion,
    topics,
}: {
    suggestion: Suggestion;
    topics: KnowledgeTopic[];
}) {
    const [promoting, setPromoting] = useState(false);

    return (
        <div className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div className="flex min-w-0 flex-1 items-start gap-3">
                    <div className="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                        <MessageCircleQuestion className="size-4 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div className="min-w-0 flex-1">
                        <p className="break-words font-medium leading-snug">{suggestion.question}</p>
                        <div className="mt-1.5 flex flex-wrap items-center gap-2">
                            <span className="text-xs text-muted-foreground">
                                {timeAgo(suggestion.created_at)}
                            </span>
                            {suggestion.chat_id && (
                                <Badge variant="outline" className="text-xs">
                                    Chat {suggestion.chat_id}
                                </Badge>
                            )}
                        </div>
                    </div>
                </div>

                <div className="flex shrink-0 items-center gap-2">
                    {!promoting && (
                        <Button
                            size="sm"
                            onClick={() => setPromoting(true)}
                            id={`promote-btn-${suggestion.id}`}
                        >
                            <Plus className="size-3.5" />
                            Add to KB
                        </Button>
                    )}

                    <Form
                        action={`${baseUrl}/${suggestion.id}/dismiss`}
                        method="post"
                        options={{ preserveScroll: true }}
                    >
                        {({ processing }) => (
                            <Button
                                type="submit"
                                size="sm"
                                variant="ghost"
                                disabled={processing}
                                id={`dismiss-btn-${suggestion.id}`}
                                title="Dismiss"
                            >
                                <X className="size-3.5" />
                            </Button>
                        )}
                    </Form>
                </div>
            </div>

            {promoting && (
                <PromotePanel
                    suggestion={suggestion}
                    topics={topics}
                    onClose={() => setPromoting(false)}
                />
            )}
        </div>
    );
}

export default function Suggestions({ suggestions, topics }: Props) {
    return (
        <>
            <Head title="Customer suggestions" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <Heading
                        title="Customer suggestions"
                        description="Questions customers asked that your AI couldn't answer. Add them to the knowledge base with one click."
                    />

                    {suggestions.length > 0 && (
                        <Form
                            action={`${baseUrl}/dismiss-all`}
                            method="post"
                            options={{ preserveScroll: true }}
                        >
                            {({ processing }) => (
                                <Button
                                    type="submit"
                                    variant="outline"
                                    size="sm"
                                    disabled={processing}
                                    className="shrink-0"
                                    id="dismiss-all-btn"
                                >
                                    <Trash2 className="size-3.5" />
                                    Dismiss all
                                </Button>
                            )}
                        </Form>
                    )}
                </div>

                {suggestions.length === 0 ? (
                    <div className="flex flex-1 flex-col items-center justify-center gap-3 rounded-lg border border-dashed border-sidebar-border/70 p-12 text-center dark:border-sidebar-border">
                        <div className="flex size-12 items-center justify-center rounded-full bg-muted">
                            <Lightbulb className="size-6 text-muted-foreground" />
                        </div>
                        <div>
                            <p className="font-medium">No pending suggestions</p>
                            <p className="mt-1 text-sm text-muted-foreground">
                                When customers ask questions your AI can't answer, they'll appear here.
                            </p>
                        </div>
                    </div>
                ) : (
                    <section className="space-y-3">
                        <p className="text-sm text-muted-foreground">
                            {suggestions.length} pending suggestion{suggestions.length !== 1 ? 's' : ''}
                        </p>

                        {suggestions.map((suggestion) => (
                            <SuggestionCard
                                key={suggestion.id}
                                suggestion={suggestion}
                                topics={topics}
                            />
                        ))}
                    </section>
                )}
            </div>
        </>
    );
}

Suggestions.layout = {
    breadcrumbs: [
        {
            title: 'Customer suggestions',
            href: baseUrl,
        },
    ],
};
