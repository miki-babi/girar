import { Form, Head } from '@inertiajs/react';
import { CheckCircle2, Plus, Save, Trash2, XCircle } from 'lucide-react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type KnowledgeSubtopic = {
    id: number;
    knowledge_topic_id: number;
    sub_topic: string;
    description: string;
    keywords: string[] | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
};

type KnowledgeTopic = {
    id: number;
    topic: string;
    keywords: string[] | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    knowledge_bases: KnowledgeSubtopic[];
};

type Props = {
    topics: KnowledgeTopic[];
};

const manageUrl = '/business-services/manage';

function keywordsText(keywords: string[] | null): string {
    return keywords?.join(', ') ?? '';
}

function ActiveToggle({
    id,
    defaultChecked,
}: {
    id: string;
    defaultChecked: boolean;
}) {
    return (
        <>
            <input type="hidden" name="is_active" value="0" />
            <label className="flex items-center gap-2 text-sm">
                <input
                    id={id}
                    type="checkbox"
                    name="is_active"
                    value="1"
                    defaultChecked={defaultChecked}
                    className="size-4 rounded border-input"
                />
                Active for AI answers
            </label>
        </>
    );
}

function StatusBadge({ isActive }: { isActive: boolean }) {
    if (isActive) {
        return (
            <Badge variant="secondary">
                <CheckCircle2 />
                Active
            </Badge>
        );
    }

    return (
        <Badge variant="outline">
            <XCircle />
            Inactive
        </Badge>
    );
}

export default function BusinessServices({ topics }: Props) {
    return (
        <>
            <Head title="Knowledge base" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                <Heading
                    title="Knowledge base"
                    description="Organize topics and subtopics with descriptions and keywords for your AI assistant."
                />

                <section className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                    <h2 className="mb-4 text-sm font-semibold">Add topic</h2>
                    <Form
                        action={`${manageUrl}/topics`}
                        method="post"
                        options={{ preserveScroll: true }}
                        resetOnSuccess
                        className="grid gap-4"
                    >
                        {({ errors, processing }) => (
                            <>
                                <div className="grid gap-4 lg:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label htmlFor="new-topic">Topic</Label>
                                        <Input
                                            id="new-topic"
                                            name="topic"
                                            placeholder="Shipping"
                                            required
                                        />
                                        <InputError message={errors.topic} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="new-topic-keywords">
                                            Keywords
                                        </Label>
                                        <Input
                                            id="new-topic-keywords"
                                            name="keywords"
                                            placeholder="delivery, freight"
                                        />
                                        <InputError message={errors.keywords} />
                                    </div>
                                </div>
                                <ActiveToggle
                                    id="new-topic-active"
                                    defaultChecked
                                />
                                <div>
                                    <Button disabled={processing}>
                                        <Plus />
                                        Add topic
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                </section>

                <section className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                    <h2 className="mb-4 text-sm font-semibold">Add subtopic</h2>
                    {topics.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            Create a topic first, then add subtopics with
                            answers underneath it.
                        </p>
                    ) : (
                        <Form
                            action={`${manageUrl}/subtopics`}
                            method="post"
                            options={{ preserveScroll: true }}
                            resetOnSuccess
                            className="grid gap-4"
                        >
                            {({ errors, processing }) => (
                                <>
                                    <div className="grid gap-4 lg:grid-cols-[minmax(180px,260px)_1fr]">
                                        <div className="grid gap-2">
                                            <Label htmlFor="knowledge_topic_id">
                                                Parent topic
                                            </Label>
                                            <select
                                                id="knowledge_topic_id"
                                                name="knowledge_topic_id"
                                                required
                                                className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                            >
                                                {topics.map((topic) => (
                                                    <option
                                                        key={topic.id}
                                                        value={topic.id}
                                                    >
                                                        {topic.topic}
                                                    </option>
                                                ))}
                                            </select>
                                            <InputError
                                                message={
                                                    errors.knowledge_topic_id
                                                }
                                            />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="sub_topic">
                                                Subtopic
                                            </Label>
                                            <Input
                                                id="sub_topic"
                                                name="sub_topic"
                                                placeholder="Express delivery"
                                                required
                                            />
                                            <InputError
                                                message={errors.sub_topic}
                                            />
                                        </div>
                                        <div className="grid gap-2 lg:col-span-2">
                                            <Label htmlFor="subtopic-keywords">
                                                Keywords
                                            </Label>
                                            <Input
                                                id="subtopic-keywords"
                                                name="keywords"
                                                placeholder="express, next-day"
                                            />
                                            <InputError
                                                message={errors.keywords}
                                            />
                                        </div>
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="description">
                                            Description
                                        </Label>
                                        <textarea
                                            id="description"
                                            name="description"
                                            required
                                            rows={4}
                                            placeholder="Next-day shipping is available in major cities..."
                                            className="min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none transition-[color,box-shadow] placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50"
                                        />
                                        <InputError
                                            message={errors.description}
                                        />
                                    </div>
                                    <ActiveToggle
                                        id="new-subtopic-active"
                                        defaultChecked
                                    />
                                    <div>
                                        <Button disabled={processing}>
                                            <Plus />
                                            Add subtopic
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    )}
                </section>

                <section className="space-y-3">
                    {topics.length === 0 ? (
                        <div className="rounded-lg border border-dashed border-sidebar-border/70 p-8 text-center text-sm text-muted-foreground dark:border-sidebar-border">
                            No topics yet.
                        </div>
                    ) : (
                        topics.map((topic) => (
                            <div key={topic.id} className="space-y-3">
                                <div className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                                    <Form
                                        action={`${manageUrl}/topics/${topic.id}`}
                                        method="put"
                                        options={{ preserveScroll: true }}
                                        className="grid gap-4"
                                    >
                                        {({ errors, processing }) => (
                                            <>
                                                <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                                    <div className="flex items-center gap-2">
                                                        <StatusBadge
                                                            isActive={
                                                                topic.is_active
                                                            }
                                                        />
                                                        <span className="font-semibold">
                                                            Topic
                                                        </span>
                                                    </div>
                                                    <Button
                                                        size="sm"
                                                        disabled={processing}
                                                    >
                                                        <Save />
                                                        Save topic
                                                    </Button>
                                                </div>
                                                <div className="grid gap-4 lg:grid-cols-2">
                                                    <div className="grid gap-2">
                                                        <Label
                                                            htmlFor={`topic-${topic.id}`}
                                                        >
                                                            Topic
                                                        </Label>
                                                        <Input
                                                            id={`topic-${topic.id}`}
                                                            name="topic"
                                                            defaultValue={
                                                                topic.topic
                                                            }
                                                            required
                                                        />
                                                        <InputError
                                                            message={
                                                                errors.topic
                                                            }
                                                        />
                                                    </div>
                                                    <div className="grid gap-2">
                                                        <Label
                                                            htmlFor={`topic-keywords-${topic.id}`}
                                                        >
                                                            Keywords
                                                        </Label>
                                                        <Input
                                                            id={`topic-keywords-${topic.id}`}
                                                            name="keywords"
                                                            defaultValue={keywordsText(
                                                                topic.keywords,
                                                            )}
                                                        />
                                                        <InputError
                                                            message={
                                                                errors.keywords
                                                            }
                                                        />
                                                    </div>
                                                </div>
                                                <ActiveToggle
                                                    id={`topic-active-${topic.id}`}
                                                    defaultChecked={
                                                        topic.is_active
                                                    }
                                                />
                                            </>
                                        )}
                                    </Form>
                                    <Form
                                        action={`${manageUrl}/topics/${topic.id}`}
                                        method="delete"
                                        options={{ preserveScroll: true }}
                                        className="mt-3"
                                    >
                                        {({ processing }) => (
                                            <Button
                                                type="submit"
                                                variant="destructive"
                                                size="sm"
                                                disabled={processing}
                                            >
                                                <Trash2 />
                                                Delete topic
                                            </Button>
                                        )}
                                    </Form>
                                </div>

                                {topic.knowledge_bases.length > 0 ? (
                                    <div className="ml-6 space-y-3 border-l-2 border-sidebar-border/70 pl-4 dark:border-sidebar-border">
                                        {topic.knowledge_bases.map(
                                            (subtopic) => (
                                                <div
                                                    key={subtopic.id}
                                                    className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border"
                                                >
                                                    <Form
                                                        action={`${manageUrl}/subtopics/${subtopic.id}`}
                                                        method="put"
                                                        options={{
                                                            preserveScroll: true,
                                                        }}
                                                        className="grid gap-4"
                                                    >
                                                        {({
                                                            errors,
                                                            processing,
                                                        }) => (
                                                            <>
                                                                <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                                                    <div className="flex items-center gap-2">
                                                                        <StatusBadge
                                                                            isActive={
                                                                                subtopic.is_active
                                                                            }
                                                                        />
                                                                        <span className="font-semibold">
                                                                            Subtopic
                                                                        </span>
                                                                    </div>
                                                                    <Button
                                                                        size="sm"
                                                                        disabled={
                                                                            processing
                                                                        }
                                                                    >
                                                                        <Save />
                                                                        Save
                                                                        subtopic
                                                                    </Button>
                                                                </div>
                                                                <div className="grid gap-4 lg:grid-cols-2">
                                                                    <div className="grid gap-2">
                                                                        <Label
                                                                            htmlFor={`subtopic-${subtopic.id}`}
                                                                        >
                                                                            Subtopic
                                                                        </Label>
                                                                        <Input
                                                                            id={`subtopic-${subtopic.id}`}
                                                                            name="sub_topic"
                                                                            defaultValue={
                                                                                subtopic.sub_topic
                                                                            }
                                                                            required
                                                                        />
                                                                        <InputError
                                                                            message={
                                                                                errors.sub_topic
                                                                            }
                                                                        />
                                                                    </div>
                                                                    <div className="grid gap-2">
                                                                        <Label
                                                                            htmlFor={`subtopic-keywords-${subtopic.id}`}
                                                                        >
                                                                            Keywords
                                                                        </Label>
                                                                        <Input
                                                                            id={`subtopic-keywords-${subtopic.id}`}
                                                                            name="keywords"
                                                                            defaultValue={keywordsText(
                                                                                subtopic.keywords,
                                                                            )}
                                                                        />
                                                                        <InputError
                                                                            message={
                                                                                errors.keywords
                                                                            }
                                                                        />
                                                                    </div>
                                                                </div>
                                                                <div className="grid gap-2">
                                                                    <Label
                                                                        htmlFor={`description-${subtopic.id}`}
                                                                    >
                                                                        Description
                                                                    </Label>
                                                                    <textarea
                                                                        id={`description-${subtopic.id}`}
                                                                        name="description"
                                                                        required
                                                                        rows={4}
                                                                        defaultValue={
                                                                            subtopic.description
                                                                        }
                                                                        className="min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none transition-[color,box-shadow] placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50"
                                                                    />
                                                                    <InputError
                                                                        message={
                                                                            errors.description
                                                                        }
                                                                    />
                                                                </div>
                                                                <ActiveToggle
                                                                    id={`subtopic-active-${subtopic.id}`}
                                                                    defaultChecked={
                                                                        subtopic.is_active
                                                                    }
                                                                />
                                                            </>
                                                        )}
                                                    </Form>
                                                    <Form
                                                        action={`${manageUrl}/subtopics/${subtopic.id}`}
                                                        method="delete"
                                                        options={{
                                                            preserveScroll: true,
                                                        }}
                                                        className="mt-3"
                                                    >
                                                        {({ processing }) => (
                                                            <Button
                                                                type="submit"
                                                                variant="destructive"
                                                                size="sm"
                                                                disabled={
                                                                    processing
                                                                }
                                                            >
                                                                <Trash2 />
                                                                Delete subtopic
                                                            </Button>
                                                        )}
                                                    </Form>
                                                </div>
                                            ),
                                        )}
                                    </div>
                                ) : (
                                    <p className="ml-6 text-sm text-muted-foreground">
                                        No subtopics for this topic yet.
                                    </p>
                                )}
                            </div>
                        ))
                    )}
                </section>
            </div>
        </>
    );
}

BusinessServices.layout = {
    breadcrumbs: [
        {
            title: 'Knowledge base',
            href: manageUrl,
        },
    ],
};
