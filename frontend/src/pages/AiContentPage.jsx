import { useState } from 'react';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import { Select } from '../components/ui/Select';

export function AiContentPage() {
  const [type, setType] = useState('caption');
  const [prompt, setPrompt] = useState('');
  const [output, setOutput] = useState('');

  function handleGenerate(event) {
    event.preventDefault();
    setOutput(`Draft ${type}: ${prompt}`);
  }

  return (
    <div className="grid gap-5 xl:grid-cols-[0.8fr_1.2fr]">
      <Card className="p-5">
        <h1 className="text-xl font-bold text-text">AI content generation</h1>
        <form className="mt-4 space-y-4" onSubmit={handleGenerate}>
          <Select label="Content type" value={type} onChange={(event) => setType(event.target.value)}>
            <option value="caption">Caption</option>
            <option value="hashtags">Hashtags</option>
            <option value="blog">Blog content</option>
          </Select>
          <label className="block">
            <span className="mb-1.5 block text-sm font-medium text-text">Prompt</span>
            <textarea className="focus-ring min-h-36 w-full rounded-md border border-border bg-panel px-3 py-3 text-sm text-text" value={prompt} onChange={(event) => setPrompt(event.target.value)} />
          </label>
          <Button type="submit" className="w-full">Generate</Button>
        </form>
      </Card>
      <Card className="p-5">
        <h2 className="text-xl font-bold text-text">Generated draft</h2>
        <textarea className="focus-ring mt-4 min-h-80 w-full rounded-md border border-border bg-canvas px-3 py-3 text-sm text-text" value={output} onChange={(event) => setOutput(event.target.value)} />
      </Card>
    </div>
  );
}
