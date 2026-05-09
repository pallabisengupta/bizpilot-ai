import { useEffect, useMemo, useState } from 'react';
import { CalendarClock, RefreshCcw, Send } from 'lucide-react';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import { Input } from '../components/ui/Input';
import { Select } from '../components/ui/Select';
import { useForm } from '../hooks/useForm';
import { scheduleService } from '../services/scheduleService';
import { getErrorMessage } from '../utils/errors';

const statusStyles = {
  queued: 'bg-warning/10 text-warning',
  processing: 'bg-brand/10 text-brand',
  published: 'bg-success/10 text-success',
  failed: 'bg-red-100 text-red-700',
};

const platforms = [
  { value: 'facebook', label: 'Facebook' },
  { value: 'instagram', label: 'Instagram' },
  { value: 'whatsapp', label: 'WhatsApp' },
];

function toLocalInputValue(date) {
  const value = new Date(date);
  value.setMinutes(value.getMinutes() - value.getTimezoneOffset());
  return value.toISOString().slice(0, 16);
}

function formatDate(value) {
  return new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value));
}

export function SchedulesPage() {
  const [schedules, setSchedules] = useState([]);
  const [meta, setMeta] = useState(null);
  const [filters, setFilters] = useState({
    status: '',
    platform: '',
    date: '',
  });
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const { values, handleChange, reset } = useForm({
    title: '',
    content: '',
    platform: 'facebook',
    scheduled_at: toLocalInputValue(new Date(Date.now() + 60 * 60 * 1000)),
    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC',
  });

  const groupedSchedules = useMemo(() => {
    return schedules.reduce((groups, schedule) => {
      const key = new Date(schedule.scheduled_at).toISOString().slice(0, 10);
      return {
        ...groups,
        [key]: [...(groups[key] || []), schedule],
      };
    }, {});
  }, [schedules]);

  async function loadSchedules(nextFilters = filters) {
    setIsLoading(true);
    setError('');

    try {
      const response = await scheduleService.list({
        ...nextFilters,
        status: nextFilters.status || undefined,
        platform: nextFilters.platform || undefined,
        date: nextFilters.date || undefined,
      });
      setSchedules(response.data || []);
      setMeta(response.meta || null);
    } catch (requestError) {
      setError(getErrorMessage(requestError));
    } finally {
      setIsLoading(false);
    }
  }

  useEffect(() => {
    loadSchedules();
  }, []);

  function handleFilterChange(event) {
    const { name, value } = event.target;
    const nextFilters = {
      ...filters,
      [name]: value,
    };
    setFilters(nextFilters);
    loadSchedules(nextFilters);
  }

  async function handleSubmit(event) {
    event.preventDefault();
    setIsSubmitting(true);
    setError('');

    try {
      await scheduleService.create({
        ...values,
        scheduled_at: new Date(values.scheduled_at).toISOString(),
        media_urls: [],
      });
      reset({
        title: '',
        content: '',
        platform: values.platform,
        scheduled_at: toLocalInputValue(new Date(Date.now() + 60 * 60 * 1000)),
        timezone: values.timezone,
      });
      await loadSchedules();
    } catch (requestError) {
      setError(getErrorMessage(requestError));
    } finally {
      setIsSubmitting(false);
    }
  }

  async function handleRetry(scheduleId) {
    setError('');

    try {
      await scheduleService.retry(scheduleId);
      await loadSchedules();
    } catch (requestError) {
      setError(getErrorMessage(requestError));
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
        <div>
          <p className="text-sm font-semibold text-brand">Scheduler</p>
          <h1 className="mt-1 text-2xl font-bold text-text">Social publishing calendar</h1>
          <p className="mt-2 max-w-2xl text-sm text-subtle">
            Queue Facebook, Instagram, and WhatsApp content, monitor publish status, and retry failed jobs.
          </p>
        </div>
        <div className="grid gap-3 sm:grid-cols-3">
          <Select label="Status" name="status" value={filters.status} onChange={handleFilterChange}>
            <option value="">All</option>
            <option value="queued">Queued</option>
            <option value="processing">Processing</option>
            <option value="published">Published</option>
            <option value="failed">Failed</option>
          </Select>
          <Select label="Platform" name="platform" value={filters.platform} onChange={handleFilterChange}>
            <option value="">All</option>
            {platforms.map((platform) => (
              <option key={platform.value} value={platform.value}>{platform.label}</option>
            ))}
          </Select>
          <Input label="Date" type="date" name="date" value={filters.date} onChange={handleFilterChange} />
        </div>
      </div>

      {error ? <div className="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div> : null}

      <section className="grid gap-5 xl:grid-cols-[0.85fr_1.15fr]">
        <Card className="p-5">
          <div className="mb-5 flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-md bg-brand/10 text-brand">
              <CalendarClock size={20} />
            </div>
            <div>
              <h2 className="text-lg font-bold text-text">Create schedule</h2>
              <p className="text-sm text-subtle">Choose platform, date, and time.</p>
            </div>
          </div>

          <form className="space-y-4" onSubmit={handleSubmit}>
            <Input label="Title" name="title" value={values.title} onChange={handleChange} placeholder="Campaign name" />
            <Select label="Platform" name="platform" value={values.platform} onChange={handleChange}>
              {platforms.map((platform) => (
                <option key={platform.value} value={platform.value}>{platform.label}</option>
              ))}
            </Select>
            <Input
              label="Date and time"
              name="scheduled_at"
              type="datetime-local"
              value={values.scheduled_at}
              onChange={handleChange}
              required
            />
            <Input label="Timezone" name="timezone" value={values.timezone} onChange={handleChange} required />
            <label className="block">
              <span className="mb-1.5 block text-sm font-medium text-text">Post content</span>
              <textarea
                name="content"
                className="focus-ring min-h-36 w-full resize-y rounded-md border border-border bg-panel px-3 py-3 text-sm text-text placeholder:text-subtle"
                value={values.content}
                onChange={handleChange}
                placeholder="Write the post or message content..."
                required
              />
            </label>
            <Button type="submit" className="w-full" isLoading={isSubmitting}>
              <Send size={17} />
              Queue schedule
            </Button>
          </form>
        </Card>

        <Card className="p-5">
          <div className="mb-5 flex items-center justify-between">
            <div>
              <h2 className="text-lg font-bold text-text">Calendar queue</h2>
              <p className="text-sm text-subtle">
                {meta?.total ?? schedules.length} scheduled item{schedules.length === 1 ? '' : 's'}
              </p>
            </div>
            <Button variant="secondary" onClick={() => loadSchedules()} disabled={isLoading}>
              <RefreshCcw size={16} />
              Refresh
            </Button>
          </div>

          <div className="space-y-5">
            {Object.keys(groupedSchedules).length === 0 ? (
              <div className="rounded-lg border border-dashed border-border bg-canvas px-4 py-10 text-center">
                <p className="font-semibold text-text">{isLoading ? 'Loading schedules...' : 'No schedules found'}</p>
                <p className="mt-1 text-sm text-subtle">Queued content will appear here by publish date.</p>
              </div>
            ) : Object.entries(groupedSchedules).map(([date, items]) => (
              <div key={date}>
                <div className="mb-3 flex items-center gap-3">
                  <div className="h-px flex-1 bg-border" />
                  <p className="text-xs font-semibold uppercase text-subtle">
                    {new Intl.DateTimeFormat(undefined, { dateStyle: 'full' }).format(new Date(`${date}T00:00:00`))}
                  </p>
                  <div className="h-px flex-1 bg-border" />
                </div>
                <div className="grid gap-3">
                  {items.map((schedule) => (
                    <article key={schedule.id} className="rounded-lg border border-border bg-canvas p-4">
                      <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div>
                          <div className="flex flex-wrap items-center gap-2">
                            <h3 className="font-semibold text-text">{schedule.title || 'Untitled post'}</h3>
                            <span className={`rounded-full px-2 py-1 text-xs font-semibold ${statusStyles[schedule.status] || 'bg-muted text-subtle'}`}>
                              {schedule.status}
                            </span>
                          </div>
                          <p className="mt-2 line-clamp-2 text-sm text-subtle">{schedule.content}</p>
                          <p className="mt-3 text-xs font-medium text-subtle">
                            {schedule.platform} · {formatDate(schedule.scheduled_at)}
                          </p>
                          {schedule.failure_reason ? (
                            <p className="mt-2 text-xs text-red-600">{schedule.failure_reason}</p>
                          ) : null}
                        </div>
                        {schedule.status === 'failed' ? (
                          <Button variant="secondary" onClick={() => handleRetry(schedule.id)}>
                            Retry
                          </Button>
                        ) : null}
                      </div>
                    </article>
                  ))}
                </div>
              </div>
            ))}
          </div>
        </Card>
      </section>
    </div>
  );
}
