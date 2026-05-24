import { Form, Head } from '@inertiajs/react';
import { CheckCircle2, Plus, Save, Trash2, XCircle } from 'lucide-react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type BusinessService = {
    id: string;
    topic: string;
    description: string;
    keywords: string[] | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
};

type Props = {
    services: BusinessService[];
};

const pageUrl = '/business-services/manage';

function keywordsText(keywords: string[] | null): string {
    return keywords?.join(', ') ?? '';
}

export default function BusinessServices({ services }: Props) {
    return (
        <>
            <Head title="Service topics" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <Heading
                        title="Service topics"
                        description="Manage the topics and answers your AI assistant can use with customers."
                    />
                </div>

                <section className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                    <Form
                        action={pageUrl}
                        method="post"
                        options={{ preserveScroll: true }}
                        resetOnSuccess
                        className="grid gap-4"
                    >
                        {({ errors, processing }) => (
                            <>
                                <div className="grid gap-4 lg:grid-cols-[minmax(180px,260px)_1fr]">
                                    <div className="grid gap-2">
                                        <Label htmlFor="topic">Topic</Label>
                                        <Input
                                            id="topic"
                                            name="topic"
                                            placeholder="Delivery Time"
                                            required
                                        />
                                        <InputError message={errors.topic} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="keywords">
                                            Keywords
                                        </Label>
                                        <Input
                                            id="keywords"
                                            name="keywords"
                                            placeholder="delivery, shipping, arrival"
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
                                        placeholder="Orders arrive in 2-3 business days..."
                                        className="min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none transition-[color,box-shadow] placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50"
                                    />
                                    <InputError
                                        message={errors.description}
                                    />
                                </div>

                                <input
                                    type="hidden"
                                    name="is_active"
                                    value="0"
                                />

                                <label className="flex items-center gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        value="1"
                                        defaultChecked
                                        className="size-4 rounded border-input"
                                    />
                                    Active for AI answers
                                </label>

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

                <section className="space-y-3">
                    {services.length === 0 ? (
                        <div className="rounded-lg border border-dashed border-sidebar-border/70 p-8 text-center text-sm text-muted-foreground dark:border-sidebar-border">
                            No service topics yet.
                        </div>
                    ) : (
                        services.map((service) => (
                            <div
                                key={service.id}
                                className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border"
                            >
                                <Form
                                    action={`${pageUrl}/${service.id}`}
                                    method="put"
                                    options={{ preserveScroll: true }}
                                    className="grid gap-4"
                                >
                                    {({ errors, processing }) => (
                                        <>
                                            <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                                <div className="flex items-center gap-2">
                                                    {service.is_active ? (
                                                        <Badge variant="secondary">
                                                            <CheckCircle2 />
                                                            Active
                                                        </Badge>
                                                    ) : (
                                                        <Badge variant="outline">
                                                            <XCircle />
                                                            Inactive
                                                        </Badge>
                                                    )}
                                                </div>

                                                <div className="flex gap-2">
                                                    <Button
                                                        size="sm"
                                                        disabled={processing}
                                                    >
                                                        <Save />
                                                        Save
                                                    </Button>
                                                </div>
                                            </div>

                                            <div className="grid gap-4 lg:grid-cols-[minmax(180px,260px)_1fr]">
                                                <div className="grid gap-2">
                                                    <Label
                                                        htmlFor={`topic-${service.id}`}
                                                    >
                                                        Topic
                                                    </Label>
                                                    <Input
                                                        id={`topic-${service.id}`}
                                                        name="topic"
                                                        defaultValue={
                                                            service.topic
                                                        }
                                                        required
                                                    />
                                                    <InputError
                                                        message={errors.topic}
                                                    />
                                                </div>

                                                <div className="grid gap-2">
                                                    <Label
                                                        htmlFor={`keywords-${service.id}`}
                                                    >
                                                        Keywords
                                                    </Label>
                                                    <Input
                                                        id={`keywords-${service.id}`}
                                                        name="keywords"
                                                        defaultValue={keywordsText(
                                                            service.keywords,
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
                                                    htmlFor={`description-${service.id}`}
                                                >
                                                    Description
                                                </Label>
                                                <textarea
                                                    id={`description-${service.id}`}
                                                    name="description"
                                                    required
                                                    rows={4}
                                                    defaultValue={
                                                        service.description
                                                    }
                                                    className="min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none transition-[color,box-shadow] placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50"
                                                />
                                                <InputError
                                                    message={
                                                        errors.description
                                                    }
                                                />
                                            </div>

                                            <input
                                                type="hidden"
                                                name="is_active"
                                                value="0"
                                            />

                                            <label className="flex items-center gap-2 text-sm">
                                                <input
                                                    type="checkbox"
                                                    name="is_active"
                                                    value="1"
                                                    defaultChecked={
                                                        service.is_active
                                                    }
                                                    className="size-4 rounded border-input"
                                                />
                                                Active for AI answers
                                            </label>
                                        </>
                                    )}
                                </Form>

                                <Form
                                    action={`${pageUrl}/${service.id}`}
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
                                            Delete
                                        </Button>
                                    )}
                                </Form>
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
            title: 'Service topics',
            href: pageUrl,
        },
    ],
};
