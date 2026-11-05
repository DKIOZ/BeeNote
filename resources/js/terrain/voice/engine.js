import { grammaire } from './phrases.js';

// Moteur remplaçable : tout le reste de l'appli ne connaît que load(), start() et stop().
export function createVoskEngine(modelUrl) {
    let modelPromise = null;
    let parts = null;

    const load = () => {
        if (!modelPromise) {
            modelPromise = import('vosk-browser')
                .then((module) => (module.createModel || module.default.createModel)(modelUrl, -2))
                .catch((error) => {
                    modelPromise = null;
                    throw error;
                });
        }

        return modelPromise;
    };

    return {
        load,

        async start(onFinal, onCoupure = () => {}) {
            if (parts) {
                return;
            }

            const model = await load();
            const stream = await navigator.mediaDevices.getUserMedia({
                video: false,
                audio: { echoCancellation: true, noiseSuppression: true, channelCount: 1 },
            });
            const context = new (window.AudioContext || window.webkitAudioContext)();
            await context.resume();

            stream.getAudioTracks().forEach((track) => {
                track.onended = () => onCoupure();
            });

            const recognizer = new model.KaldiRecognizer(context.sampleRate, grammaire());
            recognizer.on('result', (message) => {
                if (message.result.text) {
                    onFinal(message.result.text);
                }
            });

            const source = context.createMediaStreamSource(stream);
            const node = context.createScriptProcessor(4096, 1, 1);
            node.onaudioprocess = (event) => recognizer.acceptWaveform(event.inputBuffer);
            const mute = context.createGain();
            mute.gain.value = 0;
            source.connect(node);
            node.connect(mute);
            mute.connect(context.destination);

            parts = { stream, context, recognizer, node };
        },

        stop() {
            if (!parts) {
                return;
            }

            parts.node.disconnect();
            parts.stream.getTracks().forEach((track) => {
                track.onended = null;
                track.stop();
            });
            parts.recognizer.remove();
            parts.context.close();
            parts = null;
        },

        get running() {
            return parts !== null;
        },
    };
}

export function micSupported() {
    return !!navigator.mediaDevices?.getUserMedia && !!(window.AudioContext || window.webkitAudioContext);
}
