"""
Original background music for GolpoBari page templates.

Every note here is composed and synthesised from scratch (no samples, no borrowed melodies),
so the tracks are free to ship with the site. Run:  python3 tools/music/synth.py public/audio
It writes one stereo WAV per track; tools/music/build.sh turns them into small MP3s.
"""
import sys, os, wave
import numpy as np

SR = 44100
rng = np.random.default_rng(7)

# ----------------------------------------------------------------- helpers
def hz(n):  # MIDI note -> frequency
    return 440.0 * 2 ** ((n - 69) / 12)

NOTE = {'C': 0, 'C#': 1, 'Db': 1, 'D': 2, 'D#': 3, 'Eb': 3, 'E': 4, 'F': 5, 'F#': 6, 'Gb': 6, 'G': 7, 'G#': 8, 'Ab': 8, 'A': 9, 'A#': 10, 'Bb': 10, 'B': 11}

def m(name):  # 'C4' -> 60
    pitch, octave = name[:-1], int(name[-1])
    return 12 * (octave + 1) + NOTE[pitch]

def env_adsr(n, a, d, s, r, sr=SR):
    a, d, r = int(a * sr), int(d * sr), int(r * sr)
    a = max(a, 1); d = max(d, 1); r = max(r, 1)
    sus = max(n - a - d - r, 0)
    e = np.concatenate([np.linspace(0, 1, a, endpoint=False), np.linspace(1, s, d, endpoint=False), np.full(sus, s), np.linspace(s, 0, r)])
    return e[:n] if len(e) >= n else np.pad(e, (0, n - len(e)))

def onepole_lp(x, cutoff):
    a = np.exp(-2 * np.pi * cutoff / SR)
    y = np.empty_like(x)
    acc = 0.0
    # vectorised enough for short signals; used on noise bursts only
    for i in range(len(x)):
        acc = (1 - a) * x[i] + a * acc
        y[i] = acc
    return y

def fft_lowpass(x, cutoff, slope=1.0):
    X = np.fft.rfft(x)
    f = np.fft.rfftfreq(len(x), 1 / SR)
    X *= 1 / (1 + (f / cutoff) ** (2 * slope))
    return np.fft.irfft(X, len(x))

def fft_bandpass(x, lo, hi):
    X = np.fft.rfft(x)
    f = np.fft.rfftfreq(len(x), 1 / SR)
    X *= (1 / (1 + (lo / np.maximum(f, 1)) ** 4)) * (1 / (1 + (f / hi) ** 4))
    return np.fft.irfft(X, len(x))

# ----------------------------------------------------------------- instruments
def karplus(freq, dur, bright=0.5, decay=0.996, pick=0.5):
    """Plucked string (guitar / sitar-ish). Block-vectorised Karplus-Strong."""
    n = int(dur * SR)
    N = max(int(SR / freq), 2)
    buf = rng.uniform(-1, 1, N)
    buf = fft_lowpass(np.pad(buf, (0, 64)), 1500 + 9000 * bright)[:N]
    # pick position comb
    p = max(int(N * pick * 0.5), 1)
    buf = buf - np.roll(buf, p) * 0.5
    out = np.empty(n + N)
    out[:N] = buf
    i = N
    while i < n + N:
        prev = out[i - N:i]
        nxt = np.empty(N)
        nxt[:-1] = 0.5 * (prev[:-1] + prev[1:])
        nxt[-1] = 0.5 * (prev[-1] + out[i - 1] if i > 0 else prev[-1])
        take = min(N, n + N - i)
        out[i:i + take] = (nxt * decay)[:take]
        i += take
    y = out[N:N + n]
    y *= env_adsr(n, 0.002, 0.01, 1.0, min(0.08, dur * 0.3))
    return y

def musicbox(freq, dur, vel=1.0):
    n = int(dur * SR)
    t = np.arange(n) / SR
    partials = [(1, 1.0, 1.6), (2.0, 0.35, 2.6), (3.01, 0.12, 4.0), (5.43, 0.08, 7.0), (8.9, 0.03, 10)]
    y = np.zeros(n)
    for ratio, amp, dk in partials:
        if freq * ratio > SR / 2.2:
            continue
        y += amp * np.sin(2 * np.pi * freq * ratio * t + rng.uniform(0, 6.28)) * np.exp(-t * dk)
    y *= env_adsr(n, 0.001, 0.02, 1.0, 0.05) * vel
    return y

def epiano(freq, dur, vel=1.0):
    """FM electric-piano: warm, bell-ish attack."""
    n = int(dur * SR)
    t = np.arange(n) / SR
    idx = 1.6 * vel * np.exp(-t * 3.5) + 0.25
    mod = np.sin(2 * np.pi * freq * t) * idx
    y = np.sin(2 * np.pi * freq * t + mod) * np.exp(-t * 1.1)
    y += 0.25 * np.sin(2 * np.pi * freq * 2 * t) * np.exp(-t * 2.4)
    tine = 0.06 * vel * np.sin(2 * np.pi * freq * 7.1 * t) * np.exp(-t * 18)
    y = (y + tine) * env_adsr(n, 0.003, 0.05, 1.0, 0.25) * vel
    return y

def pad(freqs, dur, bright=0.35):
    n = int(dur * SR)
    t = np.arange(n) / SR
    y = np.zeros(n)
    for f in freqs:
        for det in (-0.12, 0.0, 0.11):
            ff = f * 2 ** (det / 12)
            for h in range(1, 7):
                y += (bright ** (h - 1)) / h * np.sin(2 * np.pi * ff * h * t + rng.uniform(0, 6.28))
    y /= max(len(freqs) * 3, 1)
    y *= env_adsr(n, min(1.2, dur * 0.4), 0.3, 0.85, min(1.4, dur * 0.4))
    return y

def flute(freq, dur, vel=1.0):
    n = int(dur * SR)
    t = np.arange(n) / SR
    vib = 1 + 0.006 * np.sin(2 * np.pi * 5.2 * t) * np.clip((t - 0.25) * 3, 0, 1)
    ph = 2 * np.pi * np.cumsum(freq * vib) / SR
    y = np.sin(ph) + 0.18 * np.sin(2 * ph) + 0.07 * np.sin(3 * ph)
    breath = fft_bandpass(rng.normal(0, 1, n), freq * 0.8, freq * 3) * 0.05
    y = (y + breath) * env_adsr(n, 0.07, 0.1, 0.85, min(0.25, dur * 0.4)) * vel
    return y

def bass_pluck(freq, dur, vel=1.0):
    n = int(dur * SR)
    t = np.arange(n) / SR
    y = np.sin(2 * np.pi * freq * t) + 0.3 * np.sin(4 * np.pi * freq * t) * np.exp(-t * 8)
    return y * np.exp(-t * 3.0) * env_adsr(n, 0.004, 0.02, 1.0, 0.06) * vel

def dhol_bass(vel=1.0, dur=0.6):
    n = int(dur * SR)
    t = np.arange(n) / SR
    f = 55 + 70 * np.exp(-t * 22)
    y = np.sin(2 * np.pi * np.cumsum(f) / SR) * np.exp(-t * 6.5)
    click = fft_bandpass(rng.normal(0, 1, n), 800, 3000) * np.exp(-t * 90) * 0.25
    return (y + click) * vel

def dhol_slap(vel=1.0, dur=0.25):
    n = int(dur * SR)
    t = np.arange(n) / SR
    tone = np.sin(2 * np.pi * 410 * t) * np.exp(-t * 26) * 0.6
    noise = fft_bandpass(rng.normal(0, 1, n), 1200, 6000) * np.exp(-t * 45) * 0.7
    return (tone + noise) * vel

def shaker(vel=1.0, dur=0.12):
    n = int(dur * SR)
    t = np.arange(n) / SR
    y = fft_bandpass(rng.normal(0, 1, n), 5000, 12000) * np.exp(-t * 40)
    y *= env_adsr(n, 0.008, 0.01, 1, 0.02)
    return y * vel * 0.5

def clap(vel=1.0):
    n = int(0.2 * SR)
    t = np.arange(n) / SR
    e = np.exp(-t * 30) + 0.6 * np.exp(-np.maximum(t - 0.012, 0) * 60) * (t > 0.012)
    return fft_bandpass(rng.normal(0, 1, n), 900, 5000) * e * vel * 0.6

def tanpura(root, dur, pattern=(7, 12, 12, 0), cycle=2.4):
    """Drone: slow repeating plucks of Pa Sa' Sa' Sa with long sustain."""
    n = int(dur * SR)
    y = np.zeros(n)
    step = cycle / len(pattern)
    t0 = 0.0
    k = 0
    while t0 < dur:
        semis = pattern[k % len(pattern)] - 12
        note = karplus(hz(root + semis), min(cycle * 1.6, dur - t0 + 0.01), bright=0.75, decay=0.9993, pick=0.13)
        s = int(t0 * SR)
        e = min(n, s + len(note))
        y[s:e] += note[:e - s] * 0.5
        t0 += step
        k += 1
    return y

# ----------------------------------------------------------------- mixing
class Track:
    def __init__(self, seconds):
        self.n = int(seconds * SR)
        self.L = np.zeros(self.n)
        self.R = np.zeros(self.n)

    def add(self, sig, at, gain=1.0, pan=0.0):
        s = int(at * SR)
        if s >= self.n:
            return
        e = min(self.n, s + len(sig))
        seg = sig[:e - s] * gain
        self.L[s:e] += seg * np.sqrt(0.5 * (1 - pan))
        self.R[s:e] += seg * np.sqrt(0.5 * (1 + pan))

    def add_wrap(self, sig, at, gain=1.0, pan=0.0):
        """Adds a note; anything ringing past the end wraps to the start so the loop is seamless."""
        s = int(at * SR) % self.n
        seg = sig * gain
        idx = (np.arange(len(seg)) + s) % self.n
        np.add.at(self.L, idx, seg * np.sqrt(0.5 * (1 - pan)))
        np.add.at(self.R, idx, seg * np.sqrt(0.5 * (1 + pan)))

    def reverb(self, wet=0.25, seconds=2.2, damp=4000):
        ln = int(seconds * SR)
        t = np.arange(ln) / SR
        out = []
        for ch, seed in ((self.L, 1), (self.R, 2)):
            r = np.random.default_rng(seed)
            ir = r.normal(0, 1, ln) * np.exp(-t * 6.9 / seconds)
            ir = fft_lowpass(ir, damp)
            ir[: int(0.012 * SR)] *= np.linspace(0, 1, int(0.012 * SR))
            ir /= np.sqrt(np.sum(ir ** 2))
            # circular convolution keeps the tail wrapped for a seamless loop
            size = self.n
            X = np.fft.rfft(ch, size)
            H = np.fft.rfft(np.pad(ir, (0, max(size - ln, 0)))[:size], size)
            wetsig = np.fft.irfft(X * H, size)
            out.append(ch * (1 - wet) + wetsig * wet * 0.9)
        self.L, self.R = out

    def master(self, target=0.89):
        st = np.stack([self.L, self.R])
        st = st - st.mean(axis=1, keepdims=True)
        # gentle bus compression (soft knee via tanh), then normalise
        peak = np.max(np.abs(st)) or 1
        st = np.tanh(st / peak * 1.4) / np.tanh(1.4)
        st *= target / np.max(np.abs(st))
        return st

def write_wav(path, st):
    data = (np.clip(st.T, -1, 1) * 32767).astype('<i2')
    with wave.open(path, 'wb') as w:
        w.setnchannels(2); w.setsampwidth(2); w.setframerate(SR)
        w.writeframes(data.tobytes())

# ----------------------------------------------------------------- compositions
def chord(root, kind='maj'):
    iv = {'maj': (0, 4, 7), 'min': (0, 3, 7), 'maj7': (0, 4, 7, 11), 'min7': (0, 3, 7, 10), 'sus2': (0, 2, 7), 'add9': (0, 4, 7, 14)}[kind]
    return [root + i for i in iv]

def prem():
    """'Premer Sur' - warm e-piano ballad, D major, 72 bpm."""
    bpm = 72; beat = 60 / bpm; bars = 16
    T = Track(bars * 4 * beat)
    prog = [('D3', 'maj7'), ('B2', 'min7'), ('G2', 'maj7'), ('A2', 'sus2'),
            ('D3', 'maj7'), ('B2', 'min7'), ('G2', 'maj7'), ('A2', 'maj'),
            ('G2', 'maj7'), ('A2', 'maj'), ('F#2', 'min7'), ('B2', 'min7'),
            ('G2', 'maj7'), ('A2', 'sus2'), ('D3', 'add9'), ('A2', 'maj')]
    arp = [0, 2, 1, 3, 2, 1, 3, 2]
    for b, (r, k) in enumerate(prog):
        notes = chord(m(r), k)
        t0 = b * 4 * beat
        T.add_wrap(bass_pluck(hz(m(r) - 12), 4 * beat), t0, 0.36)
        T.add_wrap(pad([hz(x + 12) for x in notes[:3]], 4 * beat + 0.8, 0.3), t0, 0.16, 0.0)
        for i, a in enumerate(arp):
            x = notes[a % len(notes)] + 12 * (1 + (a >= len(notes)))
            T.add_wrap(epiano(hz(x), 1.6, 0.55 + 0.15 * (i % 4 == 0)), t0 + i * beat / 2, 0.32, -0.25 + 0.5 * (i % 2))
    mel = [  # (bar, beat, note, beats) second half melody
        (8, 0, 'A5', 1.5), (8, 1.5, 'B5', 0.5), (8, 2, 'A5', 1), (8, 3, 'F#5', 1),
        (9, 0, 'E5', 2), (9, 2, 'C#5', 1), (9, 3, 'E5', 1),
        (10, 0, 'F#5', 1.5), (10, 1.5, 'E5', 0.5), (10, 2, 'D5', 1), (10, 3, 'C#5', 1),
        (11, 0, 'D5', 2), (11, 2, 'F#5', 2),
        (12, 0, 'B5', 1.5), (12, 1.5, 'A5', 0.5), (12, 2, 'G5', 1), (12, 3, 'F#5', 1),
        (13, 0, 'E5', 3), (13, 3, 'A4', 1),
        (14, 0, 'D5', 1), (14, 1, 'E5', 1), (14, 2, 'F#5', 2),
        (15, 0, 'E5', 4),
    ]
    for bar, bt, nn, ln in mel:
        T.add_wrap(musicbox(hz(m(nn)), ln * beat + 1.2, 0.9), (bar * 4 + bt) * beat, 0.3, 0.15)
    T.reverb(0.32, 2.6, 5000)
    return T.master()

def khushi():
    """'Khushir Din' - bouncy birthday tune, C major, 116 bpm, marimba-ish box + claps."""
    bpm = 116; beat = 60 / bpm; bars = 24
    T = Track(bars * 4 * beat)
    prog = ['C', 'G', 'A', 'F'] * 2 + ['F', 'G', 'E', 'A'] + ['D', 'G', 'C', 'C'] + ['C', 'G', 'A', 'F'] + ['F', 'G', 'C', 'G']
    kinds = {'A': 'min', 'E': 'min', 'D': 'min'}
    for b, r in enumerate(prog):
        root = m(r + '3')
        notes = chord(root, kinds.get(r, 'maj'))
        t0 = b * 4 * beat
        for q in range(4):
            T.add_wrap(bass_pluck(hz(root - 12 + (7 if q == 2 else 0)), beat * 0.9, 0.9), t0 + q * beat, 0.5)
            # off-beat chord stabs
            for x in notes:
                T.add_wrap(karplus(hz(x + 12), 0.35, 0.6, 0.99), t0 + (q + 0.5) * beat, 0.11, 0.3)
        T.add_wrap(clap(0.8), t0 + beat, 0.5, -0.1); T.add_wrap(clap(0.8), t0 + 3 * beat, 0.5, -0.1)
        for e in range(8):
            T.add_wrap(shaker(0.6 + 0.4 * (e % 2)), t0 + e * beat / 2, 0.35, 0.4)
    phrase_a = [('E5', 0.5), ('G5', 0.5), ('C6', 1), ('B5', 0.5), ('G5', 0.5), ('A5', 1),
                ('A5', 0.5), ('C6', 0.5), ('E6', 1), ('D6', 0.5), ('C6', 0.5), ('A5', 1),
                ('F5', 0.5), ('A5', 0.5), ('C6', 1), ('B5', 0.5), ('A5', 0.5), ('G5', 1),
                ('E5', 0.5), ('F5', 0.5), ('G5', 0.5), ('A5', 0.5), ('G5', 2)]
    phrase_b = [('A5', 1), ('G5', 0.5), ('F5', 0.5), ('E5', 1), ('C5', 1),
                ('D5', 0.5), ('E5', 0.5), ('F5', 0.5), ('G5', 0.5), ('A5', 2),
                ('B5', 0.5), ('A5', 0.5), ('G5', 1), ('E5', 0.5), ('G5', 0.5), ('C6', 1),
                ('C6', 0.5), ('D6', 0.5), ('E6', 1), ('C6', 2)]
    def play(phrase, start_bar):
        t = start_bar * 4 * beat
        for nn, ln in phrase:
            T.add_wrap(musicbox(hz(m(nn)), ln * beat + 0.6, 1.0), t, 0.42, -0.15)
            t += ln * beat
    play(phrase_a, 0); play(phrase_a, 4); play(phrase_b, 8); play(phrase_b, 12); play(phrase_a, 16); play(phrase_b, 20)
    T.reverb(0.2, 1.6, 6000)
    return T.master()

def smriti():
    """'Smritir Pata' - nostalgic music box, A minor, 66 bpm."""
    bpm = 66; beat = 60 / bpm; bars = 14
    T = Track(bars * 4 * beat)
    prog = [('A2', 'min'), ('F2', 'maj7'), ('C3', 'maj'), ('G2', 'maj'),
            ('A2', 'min'), ('F2', 'maj7'), ('D3', 'min7'), ('E2', 'maj'),
            ('F2', 'maj7'), ('G2', 'maj'), ('E2', 'min7'), ('A2', 'min'),
            ('D3', 'min7'), ('E2', 'maj')]
    for b, (r, k) in enumerate(prog):
        notes = chord(m(r), k)
        t0 = b * 4 * beat
        T.add_wrap(pad([hz(x + 12) for x in notes[:3]], 4 * beat + 1.0, 0.22), t0, 0.2)
        T.add_wrap(bass_pluck(hz(m(r) - 12), 4 * beat, 0.7), t0, 0.28)
        seq = [0, 1, 2, 3 if len(notes) > 3 else 1, 2, 1]
        for i in range(6):
            x = notes[seq[i] % len(notes)] + 24
            T.add_wrap(musicbox(hz(x), 2.2, 0.8), t0 + i * (4 * beat / 6), 0.28, -0.3 + 0.12 * i)
    mel = [(2, 0, 'E6', 2), (2, 2, 'D6', 1), (2, 3, 'C6', 1), (3, 0, 'B5', 3), (3, 3, 'G5', 1),
           (4, 0, 'C6', 2), (4, 2, 'B5', 1), (4, 3, 'A5', 1), (5, 0, 'A5', 2), (5, 2, 'C6', 2),
           (6, 0, 'F6', 2), (6, 2, 'E6', 1), (6, 3, 'D6', 1), (7, 0, 'E6', 4),
           (10, 0, 'G6', 1.5), (10, 1.5, 'E6', 0.5), (10, 2, 'D6', 2), (11, 0, 'C6', 2), (11, 2, 'B5', 1), (11, 3, 'A5', 1),
           (12, 0, 'D6', 2), (12, 2, 'C6', 2), (13, 0, 'B5', 2), (13, 2, 'G#5', 2)]
    for bar, bt, nn, ln in mel:
        T.add_wrap(epiano(hz(m(nn) - 12), ln * beat + 1.0, 0.7), (bar * 4 + bt) * beat, 0.22, 0.2)
    T.reverb(0.38, 3.0, 4500)
    return T.master()

def utsob():
    """'Utsober Rong' - festive dhol groove with bansuri, G major pentatonic, 96 bpm."""
    bpm = 96; beat = 60 / bpm; bars = 20
    T = Track(bars * 4 * beat)
    T.add_wrap(tanpura(m('G3'), bars * 4 * beat, pattern=(7, 12, 12, 0), cycle=4 * beat), 0, 0.28)
    # kaharwa-like 8 beat groove: dha ge na ti | na ka dhi na
    groove = [(0, 'b'), (0.75, 's'), (1, 's'), (1.5, 'b'), (2, 'b'), (2.5, 's'), (3, 's'), (3.5, 's')]
    for b in range(bars):
        t0 = b * 4 * beat
        for pos, kind in groove:
            v = 1.0 if pos in (0, 2) else 0.7
            T.add_wrap(dhol_bass(v) if kind == 'b' else dhol_slap(v), t0 + pos * beat, 0.55 if kind == 'b' else 0.32, -0.2 if kind == 'b' else 0.25)
        for e in range(8):
            T.add_wrap(shaker(0.5 + 0.5 * (e % 2 == 1)), t0 + e * beat / 2, 0.25, 0.5)
        chords = [('G2', 'maj'), ('C3', 'maj'), ('D3', 'maj'), ('G2', 'maj')]
        r, k = chords[b % 4]
        for x in chord(m(r), k):
            T.add_wrap(karplus(hz(x + 12), 0.9, 0.85, 0.996, 0.12), t0, 0.12, -0.35)
            T.add_wrap(karplus(hz(x + 12), 0.6, 0.85, 0.995, 0.12), t0 + 2.5 * beat, 0.09, -0.35)
    flute_a = [('D5', 1), ('E5', 0.5), ('G5', 0.5), ('A5', 1), ('G5', 1),
               ('E5', 0.5), ('D5', 0.5), ('E5', 1), ('G5', 2),
               ('B5', 1), ('A5', 0.5), ('G5', 0.5), ('A5', 1), ('E5', 1),
               ('G5', 0.5), ('E5', 0.5), ('D5', 1), ('D5', 2)]
    flute_b = [('G5', 0.5), ('A5', 0.5), ('B5', 1), ('D6', 1), ('B5', 1),
               ('A5', 0.5), ('B5', 0.5), ('A5', 0.5), ('G5', 0.5), ('E5', 2),
               ('D5', 0.5), ('E5', 0.5), ('G5', 1), ('A5', 0.5), ('B5', 0.5), ('A5', 1),
               ('G5', 4)]
    def play(ph, bar, gain=0.36):
        t = bar * 4 * beat
        for nn, ln in ph:
            T.add_wrap(flute(hz(m(nn)), ln * beat * 0.98, 0.9), t, gain, 0.1)
            t += ln * beat
    play(flute_a, 4); play(flute_b, 8); play(flute_a, 12); play(flute_b, 16)
    T.reverb(0.22, 1.8, 5500)
    return T.master()

def prodip():
    """'Shanto Prodip' - calm drone and slow bansuri, C, for remembrance."""
    bpm = 56; beat = 60 / bpm; bars = 12
    T = Track(bars * 4 * beat)
    T.add_wrap(tanpura(m('C3'), bars * 4 * beat, pattern=(7, 12, 12, 0), cycle=4 * beat), 0, 0.4)
    for b in range(bars):
        r = [('C3', 'sus2'), ('A2', 'min7'), ('F2', 'maj7'), ('G2', 'sus2')][b % 4]
        T.add_wrap(pad([hz(x + 12) for x in chord(m(r[0]), r[1])[:3]], 4 * beat + 1.5, 0.2), b * 4 * beat, 0.18)
    mel = [(1, 0, 'G5', 3), (1, 3, 'E5', 1), (2, 0, 'D5', 2), (2, 2, 'C5', 2), (3, 0, 'D5', 4),
           (5, 0, 'E5', 2), (5, 2, 'G5', 2), (6, 0, 'A5', 3), (6, 3, 'G5', 1), (7, 0, 'E5', 4),
           (9, 0, 'G5', 2), (9, 2, 'A5', 1), (9, 3, 'C6', 1), (10, 0, 'A5', 2), (10, 2, 'G5', 2), (11, 0, 'C5', 4)]
    for bar, bt, nn, ln in mel:
        T.add_wrap(flute(hz(m(nn)), ln * beat * 0.97, 0.8), (bar * 4 + bt) * beat, 0.34, 0.1)
    T.reverb(0.45, 3.4, 4200)
    return T.master()

def nouka():
    """'Noukar Gaan' - lilting acoustic folk in 6/8 with a river-boat flute, G, 70 bpm (dotted quarter)."""
    pulse = 60 / 70 / 3  # one eighth note
    bars = 28
    T = Track(bars * 6 * pulse)
    prog = [('G2', 'maj'), ('E2', 'min'), ('C3', 'maj'), ('D3', 'maj')] * 6 + [('C3', 'maj'), ('D3', 'maj'), ('G2', 'maj'), ('G2', 'maj')]
    strum = [(0, 1.0, 'd'), (2, 0.6, 'u'), (3, 0.85, 'd'), (5, 0.55, 'u')]
    for b, (r, k) in enumerate(prog):
        notes = chord(m(r), k)
        voicing = [notes[0] - 12, notes[0], notes[1], notes[2], notes[0] + 12, notes[1] + 12]
        t0 = b * 6 * pulse
        for pos, vel, dirn in strum:
            order = voicing if dirn == 'd' else voicing[::-1][:4]
            for i, x in enumerate(order):
                T.add_wrap(karplus(hz(x + 12), 1.4, 0.3, 0.9975, 0.3), t0 + pos * pulse + i * 0.011, 0.11 * vel, -0.3 + 0.1 * i)
    mel = [('D5', 3), ('E5', 1), ('D5', 1), ('B4', 1), ('D5', 6),
           ('E5', 2), ('G5', 1), ('E5', 2), ('D5', 1), ('B4', 3), ('A4', 3),
           ('G4', 2), ('A4', 1), ('B4', 2), ('D5', 1), ('E5', 3), ('D5', 3),
           ('B4', 2), ('A4', 1), ('G4', 3), ('G4', 6)]
    for start in (4, 12, 20):
        t = start * 6 * pulse
        for nn, ln in mel:
            T.add_wrap(flute(hz(m(nn) + 12), ln * pulse * 0.97, 0.85), t, 0.3, 0.2)
            t += ln * pulse
    T.reverb(0.3, 2.4, 5000)
    return T.master()

TRACKS = {'prem': prem, 'khushi': khushi, 'smriti': smriti, 'utsob': utsob, 'prodip': prodip, 'nouka': nouka}

if __name__ == '__main__':
    out = sys.argv[1] if len(sys.argv) > 1 else '.'
    only = sys.argv[2:] or list(TRACKS)
    os.makedirs(out, exist_ok=True)
    for name in only:
        st = TRACKS[name]()
        write_wav(os.path.join(out, name + '.wav'), st)
        print(name, round(st.shape[1] / SR, 1), 's')
