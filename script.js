const promptTemplate = document.getElementById('prompt-option');
const promptOptionsDisplay = document.getElementById('prompt-options-display');
const promptTitleElement = document.getElementById('prompt-title');
const promptTextarea = document.getElementById('prompt');
const askButton = document.getElementById('askChatGPT');
const saveButton = document.getElementById('saveNewPrompt');

const selectPrompt = (promptId) => {
    fetch(`http://localhost:8000/composite_prompts/${promptId}/expanded`)
        .then(response => response.json())
        .then(prompt => {
            promptTextarea.value = prompt.fragments.reduce((acc, fragment) => {
                return `${acc} \n\n${fragment.content}`;
            }, '');
        });
}

fetch(`http://localhost:8000/composite_prompts`)
    .then(response => response.json())
    .then(composite_prompts => {
        for (const composite_prompt of composite_prompts) {
            const template = promptTemplate.content.cloneNode(true);
            template.querySelector('h2').innerText = composite_prompt.title;
            template.querySelector('p').innerText = composite_prompt.description;
            template.querySelector('button').addEventListener('click', () => {selectPrompt(composite_prompt.id)});
            promptOptionsDisplay.appendChild(template);
        }
    });

askButton.addEventListener('click', () => {
    window.location.href = `https://chat.openai.com/?q=${promptTextarea.value}`;
});

saveButton.addEventListener('click', async () => {
    const newPrompt = await fetch(`http://localhost:8000/composite_prompts`, {
        method: 'POST',
        body: JSON.stringify({
            "author_id": 1,
            "title": "New Prompt",
            "description": "default description"
        })
    })
        .then(response => response.json())
        .then(data => {
            console.log('Success:', data);
            return data;
        });
    const newFragment = await fetch(`http://localhost:8000/prompt_fragments`, {
        method: 'POST',
        body: JSON.stringify({
            "author_id": 1,
            "content": promptTextarea.value,
            "description": "default description fragment",
        })
    })
        .then(response => response.json())
        .then(data => {
            console.log('Success:', data);
            return data
        });

    fetch(`http://localhost:8000/composite_prompts/${newPrompt.id}/fragments/${newFragment.id}`, {
        method: 'POST',
        body: JSON.stringify({
            "order_index": 0
        })
    });
});