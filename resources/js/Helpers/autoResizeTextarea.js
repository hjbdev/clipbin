/**
 * Automatically resize a textarea to when the content changes
 * @param {HTMLElement} textarea - The textarea
 */
export default function autoResizeTextarea(textarea) {
    textarea.setAttribute(
        "style",
        "height:" + textarea.scrollHeight + "px;overflow-y:hidden;"
    );
    textarea.addEventListener("input", onInput, false);

    function onInput() {
        textarea.style.height = "auto";
        textarea.style.height = textarea.scrollHeight + "px";
    }
}
