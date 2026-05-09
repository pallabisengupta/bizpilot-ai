import { useEffect, useMemo, useState } from 'react';
import { CheckCircle2, Filter, Plus, StickyNote, Target, UserRoundCheck, UsersRound } from 'lucide-react';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import { Input } from '../components/ui/Input';
import { Select } from '../components/ui/Select';
import { useForm } from '../hooks/useForm';
import { leadService } from '../services/leadService';
import { getErrorMessage } from '../utils/errors';
import { useApp } from '../store/AppContext';

const statuses = ['new', 'contacted', 'qualified', 'won', 'lost'];
const statusStyles = {
  new: 'bg-brand/10 text-brand',
  contacted: 'bg-warning/10 text-warning',
  qualified: 'bg-accent/10 text-accent',
  won: 'bg-success/10 text-success',
  lost: 'bg-red-100 text-red-700',
};

export function LeadsPage() {
  const { user } = useApp();
  const [leads, setLeads] = useState([]);
  const [stats, setStats] = useState({ total_leads: 0, new_leads: 0, conversion_rate: 0 });
  const [selectedLead, setSelectedLead] = useState(null);
  const [activities, setActivities] = useState([]);
  const [filters, setFilters] = useState({ status: '', source: '', search: '' });
  const [note, setNote] = useState('');
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const { values, handleChange, reset } = useForm({
    name: '',
    email: '',
    phone: '',
    company_name: '',
    source: 'manual',
    status: 'new',
    priority: 'normal',
    notes: '',
  });

  const metricCards = useMemo(() => [
    { label: 'Total leads', value: stats.total_leads, icon: UsersRound },
    { label: 'New leads', value: stats.new_leads, icon: Target },
    { label: 'Conversion rate', value: `${stats.conversion_rate}%`, icon: CheckCircle2 },
  ], [stats]);

  async function loadLeads(nextFilters = filters) {
    setIsLoading(true);
    setError('');

    try {
      const [statsData, leadData] = await Promise.all([
        leadService.stats(),
        leadService.list({
          ...nextFilters,
          status: nextFilters.status || undefined,
          source: nextFilters.source || undefined,
          search: nextFilters.search || undefined,
        }),
      ]);
      setStats(statsData);
      setLeads(leadData.data || []);
      if (!selectedLead && leadData.data?.[0]) {
        setSelectedLead(leadData.data[0]);
        loadActivities(leadData.data[0].id);
      }
    } catch (requestError) {
      setError(getErrorMessage(requestError));
    } finally {
      setIsLoading(false);
    }
  }

  async function loadActivities(leadId) {
    try {
      const response = await leadService.activities(leadId);
      setActivities(response.data || []);
    } catch (requestError) {
      setError(getErrorMessage(requestError));
    }
  }

  useEffect(() => {
    loadLeads();
  }, []);

  function handleFilterChange(event) {
    const nextFilters = { ...filters, [event.target.name]: event.target.value };
    setFilters(nextFilters);
    loadLeads(nextFilters);
  }

  async function handleCreate(event) {
    event.preventDefault();
    setError('');

    try {
      await leadService.create(values);
      reset({
        name: '',
        email: '',
        phone: '',
        company_name: '',
        source: 'manual',
        status: 'new',
        priority: 'normal',
        notes: '',
      });
      await loadLeads();
    } catch (requestError) {
      setError(getErrorMessage(requestError));
    }
  }

  async function handleStatusChange(lead, status) {
    try {
      const data = await leadService.update(lead.id, { status });
      setSelectedLead(data.lead);
      await loadLeads();
      await loadActivities(lead.id);
    } catch (requestError) {
      setError(getErrorMessage(requestError));
    }
  }

  async function handleAssignToMe(lead) {
    try {
      const data = await leadService.assign(lead.id, user?.id);
      setSelectedLead(data.lead);
      await loadLeads();
      await loadActivities(lead.id);
    } catch (requestError) {
      setError(getErrorMessage(requestError));
    }
  }

  async function handleAddNote(event) {
    event.preventDefault();
    if (!selectedLead || !note.trim()) return;

    try {
      const data = await leadService.addNote(selectedLead.id, note);
      setNote('');
      setSelectedLead(data.lead);
      await loadActivities(selectedLead.id);
    } catch (requestError) {
      setError(getErrorMessage(requestError));
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
        <div>
          <p className="text-sm font-semibold text-brand">Lead CRM</p>
          <h1 className="mt-1 text-2xl font-bold text-text">Manage leads and follow-ups</h1>
          <p className="mt-2 max-w-2xl text-sm text-subtle">
            Track lead status, assignments, notes, and activity history from WhatsApp, social, and manual sources.
          </p>
        </div>
        <div className="grid gap-3 sm:grid-cols-3">
          <Select label="Status" name="status" value={filters.status} onChange={handleFilterChange}>
            <option value="">All</option>
            {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
          </Select>
          <Select label="Source" name="source" value={filters.source} onChange={handleFilterChange}>
            <option value="">All</option>
            <option value="manual">Manual</option>
            <option value="whatsapp">WhatsApp</option>
            <option value="facebook">Facebook</option>
            <option value="instagram">Instagram</option>
          </Select>
          <Input label="Search" name="search" value={filters.search} onChange={handleFilterChange} placeholder="Name, email, phone" />
        </div>
      </div>

      {error ? <div className="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div> : null}

      <section className="grid gap-4 md:grid-cols-3">
        {metricCards.map((metric) => {
          const Icon = metric.icon;
          return (
            <Card key={metric.label} className="p-5">
              <div className="flex items-start justify-between">
                <div>
                  <p className="text-sm text-subtle">{metric.label}</p>
                  <p className="mt-2 text-3xl font-bold text-text">{metric.value}</p>
                </div>
                <div className="flex h-10 w-10 items-center justify-center rounded-md bg-brand/10 text-brand">
                  <Icon size={20} />
                </div>
              </div>
            </Card>
          );
        })}
      </section>

      <section className="grid gap-5 xl:grid-cols-[0.75fr_1fr_0.85fr]">
        <Card className="p-5">
          <div className="mb-4 flex items-center gap-2">
            <Plus size={18} className="text-brand" />
            <h2 className="text-lg font-bold text-text">Add lead</h2>
          </div>
          <form className="space-y-3" onSubmit={handleCreate}>
            <Input label="Name" name="name" value={values.name} onChange={handleChange} />
            <Input label="Email" name="email" type="email" value={values.email} onChange={handleChange} />
            <Input label="Phone" name="phone" value={values.phone} onChange={handleChange} />
            <Input label="Company" name="company_name" value={values.company_name} onChange={handleChange} />
            <Select label="Source" name="source" value={values.source} onChange={handleChange}>
              <option value="manual">Manual</option>
              <option value="whatsapp">WhatsApp</option>
              <option value="facebook">Facebook</option>
              <option value="instagram">Instagram</option>
            </Select>
            <Select label="Status" name="status" value={values.status} onChange={handleChange}>
              {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
            </Select>
            <label className="block">
              <span className="mb-1.5 block text-sm font-medium text-text">Notes</span>
              <textarea
                name="notes"
                className="focus-ring min-h-24 w-full rounded-md border border-border bg-panel px-3 py-2 text-sm text-text"
                value={values.notes}
                onChange={handleChange}
              />
            </label>
            <Button type="submit" className="w-full">Create lead</Button>
          </form>
        </Card>

        <Card className="p-5">
          <div className="mb-4 flex items-center justify-between">
            <div className="flex items-center gap-2">
              <Filter size={18} className="text-brand" />
              <h2 className="text-lg font-bold text-text">Lead list</h2>
            </div>
            <span className="text-xs text-subtle">{isLoading ? 'Loading...' : `${leads.length} shown`}</span>
          </div>
          <div className="space-y-3">
            {leads.length === 0 ? (
              <div className="rounded-lg border border-dashed border-border bg-canvas px-4 py-10 text-center">
                <p className="font-semibold text-text">No leads found</p>
                <p className="mt-1 text-sm text-subtle">Create a lead or adjust filters.</p>
              </div>
            ) : leads.map((lead) => (
              <button
                key={lead.id}
                className={`focus-ring w-full rounded-lg border p-4 text-left transition ${
                  selectedLead?.id === lead.id ? 'border-brand bg-brand/5' : 'border-border bg-canvas hover:bg-muted'
                }`}
                onClick={() => {
                  setSelectedLead(lead);
                  loadActivities(lead.id);
                }}
              >
                <div className="flex items-start justify-between gap-3">
                  <div>
                    <p className="font-semibold text-text">{lead.name || lead.company_name || 'Unnamed lead'}</p>
                    <p className="mt-1 text-sm text-subtle">{lead.email || lead.phone || 'No contact added'}</p>
                  </div>
                  <span className={`rounded-full px-2 py-1 text-xs font-semibold ${statusStyles[lead.status]}`}>
                    {lead.status}
                  </span>
                </div>
                <p className="mt-3 text-xs font-medium text-subtle">{lead.source} · {lead.assigned_user?.name || 'Unassigned'}</p>
              </button>
            ))}
          </div>
        </Card>

        <Card className="p-5">
          <h2 className="text-lg font-bold text-text">Lead detail</h2>
          {selectedLead ? (
            <div className="mt-4 space-y-5">
              <div>
                <p className="text-xl font-bold text-text">{selectedLead.name || 'Unnamed lead'}</p>
                <p className="text-sm text-subtle">{selectedLead.company_name || 'No company'}</p>
              </div>
              <Select label="Status" value={selectedLead.status} onChange={(event) => handleStatusChange(selectedLead, event.target.value)}>
                {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
              </Select>
              <Button variant="secondary" className="w-full" onClick={() => handleAssignToMe(selectedLead)}>
                <UserRoundCheck size={17} />
                Assign to me
              </Button>
              <form className="space-y-3" onSubmit={handleAddNote}>
                <label className="block">
                  <span className="mb-1.5 block text-sm font-medium text-text">Add note</span>
                  <textarea
                    className="focus-ring min-h-24 w-full rounded-md border border-border bg-panel px-3 py-2 text-sm text-text"
                    value={note}
                    onChange={(event) => setNote(event.target.value)}
                  />
                </label>
                <Button type="submit" variant="secondary" className="w-full">
                  <StickyNote size={17} />
                  Save note
                </Button>
              </form>
              <div>
                <h3 className="mb-3 text-sm font-bold text-text">Activity timeline</h3>
                <div className="space-y-3">
                  {activities.length === 0 ? (
                    <p className="text-sm text-subtle">No activity yet.</p>
                  ) : activities.map((activity) => (
                    <div key={activity.id} className="rounded-md border border-border bg-canvas p-3">
                      <p className="text-sm font-semibold text-text">{activity.title}</p>
                      <p className="mt-1 text-sm text-subtle">{activity.description}</p>
                      <p className="mt-2 text-xs text-subtle">{new Date(activity.occurred_at).toLocaleString()}</p>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          ) : (
            <p className="mt-4 text-sm text-subtle">Select a lead to view details.</p>
          )}
        </Card>
      </section>
    </div>
  );
}
