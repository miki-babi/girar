import { Head, Link } from '@inertiajs/react';
import { CalendarCheck, Lightbulb, MessagesSquare } from 'lucide-react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';

const tools = [
    {
        title: 'Knowledge base',
        description:
            'Edit the business information your AI assistant uses in customer replies.',
        href: '/business-services/manage',
        icon: MessagesSquare,
        action: 'Manage knowledge',
    },
    {
        title: 'Customer suggestions',
        description:
            'Review unanswered customer questions and promote useful answers.',
        href: '/suggestions/manage',
        icon: Lightbulb,
        action: 'Review suggestions',
    },
    {
        title: 'Booking links',
        description:
            'Choose the single active scheduling link shared by the booking tool.',
        href: '/booking-links/manage',
        icon: CalendarCheck,
        action: 'Manage booking links',
    },
];

export default function Dashboard() {
    return (
        <>
            <Head title="Dashboard" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                <Heading
                    title="Dashboard"
                    description="Manage the content and links your AI assistant uses with customers."
                />

                <section className="grid gap-4 md:grid-cols-3">
                    {tools.map((tool) => {
                        const Icon = tool.icon;

                        return (
                            <div
                                key={tool.href}
                                className="flex min-h-48 flex-col justify-between rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border"
                            >
                                <div className="space-y-3">
                                    <div className="flex size-10 items-center justify-center rounded-md bg-muted">
                                        <Icon className="size-5 text-muted-foreground" />
                                    </div>
                                    <div>
                                        <h2 className="font-semibold">
                                            {tool.title}
                                        </h2>
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            {tool.description}
                                        </p>
                                    </div>
                                </div>

                                <Button asChild className="mt-5 w-fit">
                                    <Link href={tool.href}>{tool.action}</Link>
                                </Button>
                            </div>
                        );
                    })}
                </section>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
