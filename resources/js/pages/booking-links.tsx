import { Form, Head } from '@inertiajs/react';
import {
    CalendarCheck,
    CheckCircle2,
    Plus,
    Save,
    Trash2,
    XCircle,
} from 'lucide-react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type BookingLink = {
    id: number;
    name: string | null;
    url: string;
    description: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
};

type Props = {
    bookingLinks: BookingLink[];
};

const manageUrl = '/booking-links/manage';

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
                Active booking link
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

export default function BookingLinks({ bookingLinks }: Props) {
    const activeLink = bookingLinks.find(
        (bookingLink) => bookingLink.is_active,
    );

    return (
        <>
            <Head title="Booking links" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <Heading
                        title="Booking links"
                        description="Manage the scheduling URLs your AI assistant can share with customers."
                    />

                    {activeLink && (
                        <a
                            href={activeLink.url}
                            target="_blank"
                            rel="noreferrer"
                            className="inline-flex h-9 shrink-0 items-center justify-center gap-2 rounded-md border border-input bg-background px-3 text-sm font-medium shadow-xs hover:bg-accent hover:text-accent-foreground"
                        >
                            <CalendarCheck className="size-4" />
                            Open active link
                        </a>
                    )}
                </div>

                <section className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                    <h2 className="mb-4 text-sm font-semibold">
                        Add booking link
                    </h2>
                    <Form
                        action={manageUrl}
                        method="post"
                        options={{ preserveScroll: true }}
                        resetOnSuccess
                        className="grid gap-4"
                    >
                        {({ errors, processing }) => (
                            <>
                                <div className="grid gap-4 lg:grid-cols-[minmax(180px,260px)_1fr]">
                                    <div className="grid gap-2">
                                        <Label htmlFor="new-name">Name</Label>
                                        <Input
                                            id="new-name"
                                            name="name"
                                            placeholder="Consultation"
                                        />
                                        <InputError message={errors.name} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="new-url">URL</Label>
                                        <Input
                                            id="new-url"
                                            name="url"
                                            type="url"
                                            placeholder="https://cal.com/your-business"
                                            required
                                        />
                                        <InputError message={errors.url} />
                                    </div>
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="new-description">
                                        Description
                                    </Label>
                                    <textarea
                                        id="new-description"
                                        name="description"
                                        rows={3}
                                        placeholder="Use this link for customer consultations."
                                        className="min-h-20 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50"
                                    />
                                    <InputError message={errors.description} />
                                </div>

                                <ActiveToggle
                                    id="new-active"
                                    defaultChecked={bookingLinks.length === 0}
                                />

                                <div>
                                    <Button disabled={processing}>
                                        <Plus />
                                        Add booking link
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                </section>

                <section className="space-y-3">
                    {bookingLinks.length === 0 ? (
                        <div className="rounded-lg border border-dashed border-sidebar-border/70 p-8 text-center text-sm text-muted-foreground dark:border-sidebar-border">
                            No booking links yet.
                        </div>
                    ) : (
                        bookingLinks.map((bookingLink) => (
                            <div
                                key={bookingLink.id}
                                className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border"
                            >
                                <Form
                                    action={`${manageUrl}/${bookingLink.id}`}
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
                                                            bookingLink.is_active
                                                        }
                                                    />
                                                    <span className="font-semibold">
                                                        {bookingLink.name ||
                                                            'Booking link'}
                                                    </span>
                                                </div>
                                                <Button
                                                    size="sm"
                                                    disabled={processing}
                                                >
                                                    <Save />
                                                    Save link
                                                </Button>
                                            </div>

                                            <div className="grid gap-4 lg:grid-cols-[minmax(180px,260px)_1fr]">
                                                <div className="grid gap-2">
                                                    <Label
                                                        htmlFor={`name-${bookingLink.id}`}
                                                    >
                                                        Name
                                                    </Label>
                                                    <Input
                                                        id={`name-${bookingLink.id}`}
                                                        name="name"
                                                        defaultValue={
                                                            bookingLink.name ??
                                                            ''
                                                        }
                                                    />
                                                    <InputError
                                                        message={errors.name}
                                                    />
                                                </div>

                                                <div className="grid gap-2">
                                                    <Label
                                                        htmlFor={`url-${bookingLink.id}`}
                                                    >
                                                        URL
                                                    </Label>
                                                    <Input
                                                        id={`url-${bookingLink.id}`}
                                                        name="url"
                                                        type="url"
                                                        defaultValue={
                                                            bookingLink.url
                                                        }
                                                        required
                                                    />
                                                    <InputError
                                                        message={errors.url}
                                                    />
                                                </div>
                                            </div>

                                            <div className="grid gap-2">
                                                <Label
                                                    htmlFor={`description-${bookingLink.id}`}
                                                >
                                                    Description
                                                </Label>
                                                <textarea
                                                    id={`description-${bookingLink.id}`}
                                                    name="description"
                                                    rows={3}
                                                    defaultValue={
                                                        bookingLink.description ??
                                                        ''
                                                    }
                                                    className="min-h-20 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50"
                                                />
                                                <InputError
                                                    message={errors.description}
                                                />
                                            </div>

                                            <ActiveToggle
                                                id={`active-${bookingLink.id}`}
                                                defaultChecked={
                                                    bookingLink.is_active
                                                }
                                            />
                                        </>
                                    )}
                                </Form>

                                <Form
                                    action={`${manageUrl}/${bookingLink.id}`}
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
                                            Delete link
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

BookingLinks.layout = {
    breadcrumbs: [
        {
            title: 'Booking links',
            href: manageUrl,
        },
    ],
};
