import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { runInNewContext } from 'node:vm';
import { test } from 'node:test';

const viewPath = fileURLToPath(new URL('../../resources/views/bimbingan/create.blade.php', import.meta.url));
const view = readFileSync(viewPath, 'utf8');
const memberScript = [...view.matchAll(/<script>\s*([\s\S]*?)\s*<\/script>/g)][0]?.[1];

assert.ok(memberScript, 'the member picker inline script should be present');

class FakeElement {
    constructor(tagName, document) {
        this.tagName = tagName.toUpperCase();
        this.document = document;
        this.children = [];
        this.parentElement = null;
        this.listeners = new Map();
        this.dataset = {};
        this.attributes = {};
        this._textContent = '';
        this.type = '';
        this.name = '';
        this.value = '';
        this.className = '';
        this.hidden = false;
        this.title = '';
        this.form = null;
    }

    get textContent() {
        return this._textContent + this.children.map((child) => child.textContent).join('');
    }

    set textContent(value) {
        this._textContent = String(value);
        for (const child of this.children) child.parentElement = null;
        this.children = [];
    }

    get nextElementSibling() {
        if (!this.parentElement) return null;
        const siblings = this.parentElement.children;
        return siblings[siblings.indexOf(this) + 1] || null;
    }

    get previousElementSibling() {
        if (!this.parentElement) return null;
        const siblings = this.parentElement.children;
        return siblings[siblings.indexOf(this) - 1] || null;
    }

    matches(selector) {
        if (selector.startsWith('.')) {
            return this.className.split(/\s+/).includes(selector.slice(1));
        }

        const dataAttribute = selector.match(/^\[data-([\w-]+)\]$/);
        if (dataAttribute) {
            const key = dataAttribute[1].replace(/-([a-z])/g, (_, letter) => letter.toUpperCase());
            return Object.hasOwn(this.dataset, key);
        }

        if (selector === 'input[type="hidden"][name="anggota_tim_ids[]"][data-member-id]') {
            return this.tagName === 'INPUT'
                && this.type === 'hidden'
                && this.name === 'anggota_tim_ids[]'
                && Object.hasOwn(this.dataset, 'memberId');
        }

        return false;
    }

    addEventListener(type, listener) {
        const listeners = this.listeners.get(type) || [];
        listeners.push(listener);
        this.listeners.set(type, listeners);
    }

    dispatch(type, properties = {}) {
        const event = {
            type,
            target: properties.target || this,
            currentTarget: null,
            bubbles: properties.bubbles ?? false,
            defaultPrevented: false,
            preventDefault() { this.defaultPrevented = true; },
            ...properties,
        };
        let current = this;

        while (current) {
            event.currentTarget = current;
            for (const listener of current.listeners.get(type) || []) listener(event);
            current = event.bubbles ? current.parentElement : null;
        }

        return event;
    }

    click() {
        return this.dispatch('click', { bubbles: true });
    }

    focus() {
        this.document.activeElement = this;
        this.dispatch('focus');
    }

    setAttribute(name, value) {
        this.attributes[name] = String(value);
    }

    removeAttribute(name) {
        delete this.attributes[name];
    }

    appendChild(child) {
        child.parentElement = this;
        this.children.push(child);
        return child;
    }

    append(...children) {
        for (const child of children) this.appendChild(child);
    }

    replaceChildren(...children) {
        for (const child of this.children) child.parentElement = null;
        this.children = [];
        for (const child of children) this.appendChild(child);
    }

    remove() {
        if (!this.parentElement) return;
        const siblings = this.parentElement.children;
        siblings.splice(siblings.indexOf(this), 1);
        this.parentElement = null;
    }

    contains(node) {
        return this === node || this.children.some((child) => child.contains(node));
    }

    querySelectorAll(selector) {
        const matches = [];

        for (const child of this.children) {
            if (child.matches(selector)) matches.push(child);
            matches.push(...child.querySelectorAll(selector));
        }

        return matches;
    }

    querySelector(selector) {
        return this.querySelectorAll(selector)[0] || null;
    }

    closest(selector) {
        let element = this;

        while (element) {
            if (element.matches(selector)) return element;
            element = element.parentElement;
        }

        return null;
    }
}

function createPickerHarness() {
    const timers = new Map();
    let nextTimer = 1;
    const document = { activeElement: null, listeners: new Map() };
    const make = (tagName) => new FakeElement(tagName, document);
    const form = make('form');
    const picker = make('div');
    const trigger = make('button');
    const triggerLabel = make('span');
    const triggerCount = make('span');
    const panel = make('div');
    const search = make('input');
    const options = make('div');
    const status = make('p');
    const list = make('div');
    const selectedCount = make('span');
    const selected = make('div');
    const error = make('p');

    trigger.dataset.memberTrigger = '';
    trigger.form = form;
    triggerLabel.dataset.memberTriggerLabel = '';
    triggerCount.dataset.memberTriggerCount = '';
    panel.dataset.memberPanel = '';
    panel.hidden = true;
    search.dataset.memberSearch = '';
    search.dataset.searchUrl = '/students';
    options.dataset.memberOptions = '';
    options.hidden = true;
    status.dataset.memberStatus = '';
    list.dataset.memberList = '';
    selectedCount.dataset.memberSelectedCount = '';
    selected.dataset.memberSelected = '';
    selected.hidden = true;
    error.dataset.memberError = '';
    error.hidden = true;

    picker.append(trigger, triggerLabel, triggerCount, panel);
    panel.append(search, options, status);
    picker.append(list, selectedCount, selected, error);

    const selectorTargets = new Map([
        ['[data-member-trigger]', trigger],
        ['[data-member-trigger-label]', triggerLabel],
        ['[data-member-trigger-count]', triggerCount],
        ['[data-member-panel]', panel],
        ['[data-member-search]', search],
        ['[data-member-options]', options],
        ['[data-member-status]', status],
        ['[data-member-list]', list],
        ['[data-member-selected-count]', selectedCount],
        ['[data-member-selected]', selected],
        ['[data-member-error]', error],
    ]);

    picker.querySelector = (selector) => selectorTargets.get(selector) || null;
    document.querySelector = (selector) => selector === '[data-member-picker]' ? picker : null;
    document.querySelectorAll = () => [];
    document.createElement = make;
    document.addEventListener = (type, listener) => {
        const listeners = document.listeners.get(type) || [];
        listeners.push(listener);
        document.listeners.set(type, listeners);
    };

    const window = {
        setTimeout(callback) {
            const id = nextTimer++;
            timers.set(id, callback);
            return id;
        },
        clearTimeout(id) {
            timers.delete(id);
        },
    };
    const students = [{ id: 42, name: 'Bagas Mahendra', nim_nip: '1301260004' }];
    const fetchCalls = [];
    const fetch = async (url) => {
        fetchCalls.push(url);
        return { ok: true, json: async () => students };
    };

    runInNewContext(memberScript, { document, window, fetch });

    return {
        document,
        error,
        fetchCalls,
        form,
        list,
        options,
        panel,
        picker,
        search,
        selected,
        selectedCount,
        status,
        timers,
        trigger,
        triggerCount,
        triggerLabel,
        async runNextTimer() {
            const entry = [...timers.entries()].at(-1);
            assert.ok(entry, 'a search timer should be scheduled');
            timers.delete(entry[0]);
            await entry[1]();
        },
    };
}

test('a click on a dynamically rendered student selects and submits its ID', async () => {
    const harness = createPickerHarness();
    const {
        document, error, fetchCalls, form, list, options, panel, search, selected,
        selectedCount, status, timers, trigger, triggerCount, triggerLabel,
    } = harness;

    assert.equal(triggerCount.textContent, '0 dipilih');
    assert.equal(selected.hidden, true);

    trigger.click();
    assert.equal(panel.hidden, false);

    search.value = 'ba';
    search.dispatch('input');
    assert.equal(timers.size, 0);
    assert.equal(fetchCalls.length, 0);
    assert.match(status.textContent, /minimal 3 karakter/);

    search.value = 'bag';
    search.dispatch('input');
    await harness.runNextTimer();

    assert.equal(fetchCalls.length, 1);
    assert.equal(fetchCalls[0], '/students?q=bag');
    assert.equal(options.children.length, 1);
    const result = options.children[0];

    assert.equal(result.dataset.studentId, '42');
    assert.equal(Object.hasOwn(result.dataset, 'memberId'), false);

    search.dispatch('keydown', { key: 'ArrowDown' });
    assert.equal(document.activeElement, result);

    result.click();

    const selectedId = list.querySelector('input[type="hidden"][name="anggota_tim_ids[]"][data-member-id]');
    assert.ok(selectedId, 'the selected student should create a hidden form field');
    assert.equal(selectedId.value, '42');
    assert.equal(selectedId.name, 'anggota_tim_ids[]');
    assert.equal(triggerCount.textContent, '1 dipilih');
    assert.equal(triggerLabel.textContent, 'Tambah anggota tim');
    assert.equal(selectedCount.textContent, '1 anggota dipilih');
    assert.equal(selected.hidden, false);
    assert.equal(list.querySelectorAll('[data-member-chip]').length, 1);
    assert.equal(options.children.length, 0);

    search.value = 'bag';
    search.dispatch('input');
    await harness.runNextTimer();
    assert.equal(options.children.length, 0);
    assert.match(status.textContent, /sudah dipilih/);

    const removeButton = list.querySelector('[data-member-remove]');
    assert.ok(removeButton);
    removeButton.click();
    assert.equal(list.querySelectorAll('[data-member-chip]').length, 0);
    assert.equal(triggerCount.textContent, '0 dipilih');
    assert.equal(selected.hidden, true);

    const invalidSubmit = form.dispatch('submit');
    assert.equal(invalidSubmit.defaultPrevented, true);
    assert.equal(error.hidden, false);
    assert.match(error.textContent, /Pilih minimal satu anggota/);

    search.value = 'bag';
    search.dispatch('input');
    await harness.runNextTimer();
    const secondResult = options.children[0];
    assert.ok(secondResult);
    secondResult.click();

    const validSubmit = form.dispatch('submit');
    assert.equal(validSubmit.defaultPrevented, false);
    assert.deepEqual(list.querySelectorAll('input[type="hidden"][name="anggota_tim_ids[]"][data-member-id]').map((input) => input.value), ['42']);
});