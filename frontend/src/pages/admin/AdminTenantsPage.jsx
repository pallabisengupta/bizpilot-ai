import { useEffect, useState } from 'react';
import { Button } from '../../components/ui/Button';
import { Card } from '../../components/ui/Card';
import { adminService } from '../../services/adminService';

export function AdminTenantsPage() {
  const [tenants, setTenants] = useState([]);

  async function loadTenants() {
    const response = await adminService.tenants();
    setTenants(response.data || []);
  }

  useEffect(() => {
    loadTenants().catch(() => {});
  }, []);

  async function updateStatus(id, action) {
    if (action === 'activate') await adminService.activateTenant(id);
    if (action === 'suspend') await adminService.suspendTenant(id);
    await loadTenants();
  }

  return (
    <Card className="p-5">
      <h1 className="text-xl font-bold text-text">Tenant management</h1>
      <div className="mt-4 space-y-3">
        {tenants.map((tenant) => (
          <div key={tenant.id} className="flex flex-col justify-between gap-3 rounded-lg border border-border bg-canvas p-4 md:flex-row md:items-center">
            <div>
              <p className="font-semibold text-text">{tenant.company_name}</p>
              <p className="text-sm text-subtle">{tenant.email || tenant.slug} · {tenant.status}</p>
            </div>
            <div className="flex gap-2">
              <Button variant="secondary" onClick={() => updateStatus(tenant.id, 'activate')}>Activate</Button>
              <Button variant="secondary" onClick={() => updateStatus(tenant.id, 'suspend')}>Suspend</Button>
            </div>
          </div>
        ))}
      </div>
    </Card>
  );
}
