const PLAN_KEY = 'bizpilot_selected_plan_id';

export function saveSelectedPlan(planId) {
  if (planId) {
    localStorage.setItem(PLAN_KEY, String(planId));
  }
}

export function getSelectedPlanId(search = '') {
  const params = new URLSearchParams(search);
  return params.get('plan') || localStorage.getItem(PLAN_KEY);
}

export function clearSelectedPlan() {
  localStorage.removeItem(PLAN_KEY);
}

export function buildAuthRedirect(path, planId) {
  const params = new URLSearchParams();

  if (planId) {
    params.set('plan', planId);
  }

  params.set('next', path);

  return params.toString();
}
