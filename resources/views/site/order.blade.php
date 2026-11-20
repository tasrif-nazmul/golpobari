@extends('layouts.site', ['headerClass' => 'hidden md:block'])

@section('content')
<div x-data="orderWizard(@js($boot))" x-cloak class="pb-40 md:pb-24">

    {{-- Progress --}}
    <div class="sticky top-0 z-40 border-b border-ink/10 bg-paper/95 backdrop-blur md:top-[4.5rem]">
        <div class="mx-auto flex max-w-3xl items-center gap-3 px-4 py-3 sm:px-6">
            <a href="{{ route('home') }}" class="md:hidden" aria-label="হোম"><x-logo class="h-8 w-8 text-ink" /></a>
            <ol class="flex flex-1 items-center gap-1.5" aria-label="অর্ডারের ধাপ">
                <template x-for="(g, i) in groups" :key="i">
                    <li class="flex flex-1 flex-col gap-1">
                        <span class="h-1.5 rounded-full transition-colors duration-500" :class="currentGroup > i + 1 ? 'bg-kolapata' : currentGroup === i + 1 ? 'bg-sindoor' : 'bg-ink/10'"></span>
                        <span class="hidden text-xs font-semibold sm:block" :class="currentGroup === i + 1 ? 'text-ink' : 'text-ink-mute'" x-text="bn(i + 1) + ' ' + g"></span>
                    </li>
                </template>
            </ol>
            <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-ink-soft ring-1 ring-ink/10" x-text="bn(step) + '/' + bn(steps.length)"></span>
        </div>
    </div>

    <div class="mx-auto max-w-3xl px-4 pt-6 transition-[max-width] sm:px-6 sm:pt-10" :class="step === 3 ? 'lg:max-w-6xl' : ''">
        @unless ($boot['ordersEnabled'])
            <div class="mb-6 rounded-2xl bg-holud/20 p-4 font-semibold text-ink">এই মুহূর্তে নতুন অর্ডার নেওয়া সাময়িকভাবে বন্ধ আছে। শীঘ্রই আবার চালু হবে।</div>
        @endunless

        <template x-if="restored && step > 1">
            <div class="mb-5 flex items-center justify-between gap-3 rounded-2xl bg-white px-4 py-3 text-sm ring-1 ring-ink/10">
                <span>আগের অর্ডারের তথ্য ফিরিয়ে আনা হয়েছে।</span>
                <button type="button" class="font-semibold text-sindoor underline underline-offset-4" @click="startOver()">নতুন করে শুরু</button>
            </div>
        </template>

        <header class="mb-6">
            <p class="eyebrow" x-text="'ধাপ ' + bn(step)"></p>
            <h1 class="mt-1 text-[1.9rem] font-extrabold leading-tight sm:text-4xl" x-text="steps[step - 1].title"></h1>
            <p class="mt-1 text-ink-soft" x-text="steps[step - 1].sub"></p>
        </header>

        {{-- STEP 1: Category --}}
        <section x-show="step === 1" x-transition.opacity.duration.300ms>
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <template x-for="c in boot.catalog" :key="c.slug">
                    <button type="button" @click="selectCategory(c.slug)"
                            class="stitch relative flex min-h-[9.5rem] flex-col justify-between rounded-[var(--radius-card)] p-4 text-left transition hover:-translate-y-0.5 sm:p-5"
                            :style="`background: color-mix(in oklab, ${c.accent} 13%, white); color: ${c.accent}`"
                            :class="categorySlug === c.slug ? 'ring-[3px] ring-ink' : ''" :aria-pressed="categorySlug === c.slug">
                        <span class="text-4xl" x-text="c.emoji"></span>
                        <span class="text-ink">
                            <span class="block text-lg font-bold leading-snug" x-text="c.name"></span>
                            <span class="mt-0.5 block text-sm leading-snug text-ink-soft" x-text="c.tagline"></span>
                        </span>
                    </button>
                </template>
            </div>
        </section>

        {{-- STEP 2: Template --}}
        <section x-show="step === 2" x-transition.opacity.duration.300ms>
            <template x-if="category && category.occasions.length">
                <div class="mb-6">
                    <p class="field-label">উপলক্ষ <span class="font-normal text-ink-mute">(ঐচ্ছিক)</span></p>
                    <div class="no-scrollbar -mx-4 flex gap-2 overflow-x-auto px-4 sm:mx-0 sm:flex-wrap sm:px-0">
                        <template x-for="o in category.occasions" :key="o">
                            <button type="button" class="chip shrink-0 !py-2 ring-1 transition" @click="occasion = occasion === o ? '' : o"
                                    :class="occasion === o ? '!bg-ink !text-paper ring-ink' : 'bg-white ring-ink/10'" :aria-pressed="occasion === o" x-text="o"></button>
                        </template>
                    </div>
                </div>
            </template>
            <div class="grid gap-4 sm:grid-cols-2">
                <template x-for="t in (category ? category.templates : [])" :key="t.slug">
                    <div class="card overflow-hidden transition" :class="templateSlug === t.slug ? 'ring-[3px] ring-ink' : ''">
                        <button type="button" class="tcard__stage group relative block w-full !aspect-[4/3.2]" style="--tint: #f3e6cf" @click="previewModal = t" :aria-label="t.name + ' প্রিভিউ'">
                            <template x-if="t.shots">
                                <span>
                                    <span class="phone phone--sm tcard__back !top-[8%] !w-[32%]"><span class="phone__screen block"><img :src="t.shots.inside" alt="" loading="lazy"></span></span>
                                    <span class="phone phone--sm tcard__front !left-[18%] !top-[10%] !w-[34%]"><span class="phone__screen block"><img :src="t.shots.cover" :alt="t.name" loading="lazy"></span></span>
                                </span>
                            </template>
                            <template x-if="!t.shots && t.preview"><img :src="t.preview" :alt="t.name" class="h-full w-full object-cover object-top" loading="lazy"></template>
                            <template x-if="!t.shots && !t.preview"><span class="grid h-full place-items-center font-serif text-2xl text-ink-mute" x-text="t.name"></span></template>
                            <span class="absolute bottom-3 right-3 z-10 inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 text-sm font-bold shadow"><svg class="h-3 w-3 text-sindoor" viewBox="0 0 24 24" fill="currentColor"><path d="M7 4.5v15l13-7.5z"/></svg> লাইভ দেখুন</span>
                        </button>
                        <div class="p-4">
                            <h3 class="font-serif text-xl font-semibold" x-text="t.name"></h3>
                            <p class="mt-0.5 text-[0.95rem] text-ink-soft" x-text="t.tagline"></p>
                            <button type="button" class="btn mt-4 w-full" :class="templateSlug === t.slug ? 'btn-ink' : 'btn-ghost'" @click="selectTemplate(t.slug)">
                                <span x-text="templateSlug === t.slug ? '✓ এটা বেছে নেওয়া হয়েছে' : 'এটা নেব'"></span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </section>

        {{-- STEP 3: The story, section by section, with a live preview that points at where each answer lands --}}
        <section x-show="step === 3" x-transition.opacity.duration.300ms>
            <div class="lg:grid lg:grid-cols-[minmax(0,1fr)_380px] lg:items-start lg:gap-8">
                <div class="space-y-5">
                    <div class="rounded-2xl bg-white p-4 text-[0.95rem] ring-1 ring-ink/10 sm:p-5">
                        <p class="font-bold">কীভাবে লিখবেন? খুব সহজ 👇</p>
                        <ol class="mt-2 space-y-1.5 text-ink-soft">
                            <li><b class="text-ink">১.</b> আপনার পেজটা উপর থেকে নিচে <b class="text-ink" x-text="bn(sections.length) + 'টা অংশে'"></b> সাজানো। নিচে প্রতিটা অংশ আলাদা বক্সে, পেজে যে ক্রমে আসবে সেই ক্রমে।</li>
                            <li><b class="text-ink">২.</b> প্রতিটা ঘরের উপরে হলুদ <span class="rounded bg-holud/30 px-1.5 text-xs font-bold text-ink">উদাহরণ</span> দেখে দেখে লিখুন।</li>
                            <li><b class="text-ink">৩.</b> <span class="lg:hidden">যেকোনো ঘরের পাশে <b class="text-sindoor">📍 পেজে কোথায়?</b> চাপলে</span><span class="hidden lg:inline">যে ঘরে লিখবেন, ডান পাশের প্রিভিউতে</span> <b class="text-sindoor">লাল দাগ দিয়ে</b> দেখাবে লেখাটা পেজের ঠিক কোথায় বসবে।</li>
                        </ol>
                    </div>

                    <template x-for="sec in sections" :key="sec.n">
                        <div class="card overflow-hidden" :id="'sec_' + sec.n">
                            <div class="flex items-start gap-4 border-b border-ink/5 bg-paper-2/60 px-5 py-4 sm:px-7">
                                <div class="flex h-[4.6rem] w-10 shrink-0 flex-col gap-[3px] rounded-[.6rem] border-[2.5px] border-ink/80 bg-white p-[3px]" aria-hidden="true" title="পেজে এই অংশের জায়গা">
                                    <template x-for="i in sec.total" :key="i"><span class="flex-1 rounded-[2px] transition" :class="i === sec.n ? 'bg-sindoor' : 'bg-ink/10'"></span></template>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold tracking-wide text-sindoor" x-text="'পেজের অংশ ' + bn(sec.n) + ' / ' + bn(sec.total)"></p>
                                    <h2 class="text-xl font-bold leading-snug"><span x-text="sec.icon"></span> <span x-text="sec.title"></span></h2>
                                    <p class="mt-0.5 text-sm leading-snug text-ink-soft"><b class="font-semibold text-ink">পেজে কোথায়:</b> <span x-text="sec.where"></span></p>
                                </div>
                            </div>

                            <div class="space-y-7 p-5 sm:p-7">
                                <template x-for="f in sec.fields.map((k) => fieldBy(k)).filter(Boolean)" :key="f.key">
                                    <div @focusin="focusField(f.key)" class="scroll-mt-24 rounded-2xl transition" :class="activeField === f.key ? 'lg:bg-sindoor/[0.04] lg:ring-2 lg:ring-sindoor/30 lg:ring-offset-8 lg:ring-offset-white' : ''">
                                        <div class="mb-1.5 flex items-start justify-between gap-3">
                                            <label class="field-label !mb-0" :for="isMedia(f) ? null : 'f_' + f.key">
                                                <span x-text="f.label"></span><span x-show="f.required" class="text-sindoor"> *</span>
                                                <span x-show="!f.required" class="text-xs font-normal text-ink-mute">(ঐচ্ছিক)</span>
                                            </label>
                                            <button type="button" x-show="f.type !== 'music'" class="shrink-0 rounded-full bg-sindoor/10 px-2.5 py-1 text-xs font-bold text-sindoor-dark transition hover:bg-sindoor/20" @click="showOnPage(f.key)">📍 পেজে কোথায়?</button>
                                        </div>
                                        <div class="mb-2.5 flex items-center gap-2.5 text-sm text-ink-mute" x-show="f.type !== 'music' && (exampleText(f) || exampleImage(f))">
                                            <span class="shrink-0 rounded bg-holud/30 px-1.5 py-0.5 text-[11px] font-bold text-ink">উদাহরণ</span>
                                            <template x-if="exampleImage(f)"><img :src="exampleImage(f)" alt="" class="h-9 w-9 shrink-0 rounded-md object-cover ring-1 ring-ink/10"></template>
                                            <span class="line-clamp-2 italic" x-text="exampleText(f) || (f.type === 'gallery' ? 'এরকম কয়েকটা ছবি' : 'এরকম একটা ছবি')"></span>
                                        </div>

                                        <template x-if="['text','email','url','phone','date','color'].includes(f.type)">
                                            <input class="input" :id="'f_' + f.key" x-model="content[f.key]" :placeholder="f.placeholder || ''"
                                                   :type="{text:'text',email:'email',url:'url',phone:'tel',date:'date',color:'color'}[f.type]"
                                                   :inputmode="f.type === 'phone' ? 'tel' : null" :maxlength="f.max_length || null"
                                                   :aria-invalid="!!fieldError(f.key)" :aria-describedby="'h_' + f.key">
                                        </template>
                                        <template x-if="f.type === 'textarea'">
                                            <div>
                                                <textarea class="input" :id="'f_' + f.key" x-model="content[f.key]" :placeholder="f.placeholder || ''" rows="5"
                                                          :maxlength="f.max_length || null" :aria-invalid="!!fieldError(f.key)"></textarea>
                                                <p class="mt-1 text-right text-xs text-ink-mute" x-show="f.max_length" x-text="bn((content[f.key] || '').length) + '/' + bn(f.max_length)"></p>
                                            </div>
                                        </template>
                                        <template x-if="f.type === 'music'">
                                            <div>
                                                <p class="mb-3 text-sm text-ink-soft" x-text="f.help"></p>
                                                <div class="grid gap-2.5 sm:grid-cols-2" role="radiogroup" :aria-label="f.label">
                                                    <template x-for="t in boot.tracks" :key="t.key">
                                                        <div role="radio" tabindex="0" :aria-checked="content[f.key] === t.key" @click="content[f.key] = t.key" @keydown.enter.prevent="content[f.key] = t.key" @keydown.space.prevent="content[f.key] = t.key"
                                                             class="flex cursor-pointer items-center gap-3 rounded-2xl bg-white p-2.5 pr-3 ring-1 transition" :class="content[f.key] === t.key ? 'bg-sindoor/[0.06] ring-2 ring-sindoor' : 'ring-ink/10 hover:ring-ink/30'">
                                                            <button type="button" @click.stop="toggleSample(t)" class="relative grid h-11 w-11 shrink-0 place-items-center rounded-full text-paper transition" :class="playing === t.key ? 'bg-sindoor' : 'bg-ink'" :aria-label="(playing === t.key ? 'থামান: ' : 'শুনে দেখুন: ') + t.title">
                                                                <svg x-show="playing !== t.key" class="ml-0.5 h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M7 4.5v15l13-7.5z"/></svg>
                                                                <span x-show="playing === t.key" class="flex h-4 items-end gap-[3px]"><i class="eq-bar"></i><i class="eq-bar [animation-delay:-.3s]"></i><i class="eq-bar [animation-delay:-.6s]"></i></span>
                                                            </button>
                                                            <span class="min-w-0 flex-1 leading-tight"><b class="block truncate" x-text="t.icon + ' ' + t.title"></b><span class="block truncate text-xs text-ink-mute" x-text="t.mood"></span></span>
                                                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full text-xs font-bold" :class="content[f.key] === t.key ? 'bg-sindoor text-white' : 'ring-1 ring-ink/20 text-transparent'">✓</span>
                                                        </div>
                                                    </template>
                                                </div>
                                                <div class="mt-2.5 grid gap-2.5 sm:grid-cols-2">
                                                    <div class="rounded-2xl bg-white p-2.5 ring-1 transition" :class="content[f.key] === 'custom' ? 'bg-sindoor/[0.06] ring-2 ring-sindoor' : 'ring-ink/10'">
                                                        <input type="file" class="sr-only" :id="'file_' + f.key" accept="audio/mpeg,audio/mp3,audio/mp4,audio/x-m4a,audio/aac,audio/ogg,audio/wav,.mp3,.m4a,.aac,.ogg,.wav" @change="onMusic(f, $event)">
                                                        <template x-if="!(media.music || []).length">
                                                            <button type="button" @click="pick(f)" class="flex w-full items-center gap-3 text-left">
                                                                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-holud/40 text-xl">🎤</span>
                                                                <span class="leading-tight"><b class="block">নিজের গান দিন</b><span class="text-xs text-ink-mute" x-text="'MP3 বা M4A, সর্বোচ্চ ' + bn(boot.limits.audio_mb) + ' MB'"></span></span>
                                                            </button>
                                                        </template>
                                                        <template x-for="(m, i) in (media.music || [])" :key="m.key">
                                                            <div class="flex items-center gap-3">
                                                                <button type="button" @click="m.id && toggleSample({ key: 'custom', url: m.url })" class="grid h-11 w-11 shrink-0 place-items-center rounded-full text-paper" :class="playing === 'custom' ? 'bg-sindoor' : 'bg-ink'" :disabled="!m.id" aria-label="আপনার গান শুনুন">
                                                                    <span x-show="m.uploading" class="text-[11px] font-bold" x-text="bn(m.progress) + '%'"></span>
                                                                    <svg x-show="!m.uploading && playing !== 'custom'" class="ml-0.5 h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M7 4.5v15l13-7.5z"/></svg>
                                                                    <span x-show="!m.uploading && playing === 'custom'" class="flex h-4 items-end gap-[3px]"><i class="eq-bar"></i><i class="eq-bar [animation-delay:-.3s]"></i><i class="eq-bar [animation-delay:-.6s]"></i></span>
                                                                </button>
                                                                <span class="min-w-0 flex-1 leading-tight"><b class="block truncate" x-text="m.name || 'আপনার গান'"></b>
                                                                    <span class="block text-xs" :class="m.error ? 'font-semibold text-sindoor-dark' : 'text-ink-mute'" x-text="m.error || (m.uploading ? 'আপলোড হচ্ছে…' : 'আপনার গান যোগ হয়েছে')"></span></span>
                                                                <button type="button" class="shrink-0 rounded-full px-2 py-1 text-xs font-semibold text-sindoor underline" @click="removeMusic(f)">বাদ দিন</button>
                                                            </div>
                                                        </template>
                                                    </div>
                                                    <button type="button" @click="content[f.key] = 'none'; stopSample()" class="flex items-center gap-3 rounded-2xl bg-white p-2.5 text-left ring-1 transition" :class="content[f.key] === 'none' ? 'bg-sindoor/[0.06] ring-2 ring-sindoor' : 'ring-ink/10 hover:ring-ink/30'">
                                                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-paper-2 text-xl">🔇</span>
                                                        <span class="leading-tight"><b class="block">গান ছাড়া</b><span class="text-xs text-ink-mute">পেজে কোনো গান বাজবে না</span></span>
                                                    </button>
                                                </div>
                                                <p class="mt-2 text-xs text-ink-mute">নিজের গান দিলে খেয়াল রাখবেন, গানটা ব্যবহারের অধিকার যেন আপনার থাকে।</p>
                                            </div>
                                        </template>
                                        <template x-if="f.type === 'select'">
                                            <select class="input" :id="'f_' + f.key" x-model="content[f.key]" :aria-invalid="!!fieldError(f.key)">
                                                <option value="">বেছে নিন</option>
                                                <template x-for="c in (f.options.choices || [])" :key="c"><option :value="c" x-text="c"></option></template>
                                            </select>
                                        </template>
                                        <template x-if="f.type === 'repeater'">
                                            <div class="space-y-3">
                                                <template x-for="(row, i) in content[f.key]" :key="i">
                                                    <div class="rounded-2xl bg-paper p-4 ring-1 ring-ink/10">
                                                        <div class="mb-2 flex items-center justify-between">
                                                            <span class="text-sm font-semibold text-ink-mute" x-text="'#' + bn(i + 1)"></span>
                                                            <span class="flex gap-1">
                                                                <button type="button" class="h-9 w-9 rounded-full text-ink-mute hover:bg-white" @click="moveRow(f, i, -1)" aria-label="উপরে">↑</button>
                                                                <button type="button" class="h-9 w-9 rounded-full text-ink-mute hover:bg-white" @click="moveRow(f, i, 1)" aria-label="নিচে">↓</button>
                                                                <button type="button" class="h-9 w-9 rounded-full text-sindoor hover:bg-white" @click="removeRow(f, i)" aria-label="মুছুন">✕</button>
                                                            </span>
                                                        </div>
                                                        <div class="grid gap-3" :class="(f.options.fields || []).length > 1 && !(f.options.fields || []).some(s => s.type === 'textarea') ? 'sm:grid-cols-2' : ''">
                                                            <template x-for="s in (f.options.fields || [])" :key="s.key">
                                                                <label class="block">
                                                                    <span class="mb-1 block text-sm font-medium text-ink-soft" x-text="s.label"></span>
                                                                    <template x-if="s.type === 'textarea'"><textarea class="input !min-h-[5.5rem]" rows="3" x-model="row[s.key]" maxlength="2000" :placeholder="rowExample(f, s.key)"></textarea></template>
                                                                    <template x-if="s.type === 'select'">
                                                                        <select class="input" x-model="row[s.key]"><option value="">—</option><template x-for="c in (s.choices || [])" :key="c"><option :value="c" x-text="c"></option></template></select>
                                                                    </template>
                                                                    <template x-if="!['textarea','select'].includes(s.type)"><input class="input" :type="s.type === 'url' ? 'url' : 'text'" x-model="row[s.key]" maxlength="255" :placeholder="rowExample(f, s.key)"></template>
                                                                </label>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                                <button type="button" class="btn btn-ghost btn-sm w-full border-2 border-dashed border-ink/15 !ring-0" @click="addRow(f)"
                                                        x-show="(content[f.key] || []).length < (f.options.max || 20)" x-text="(content[f.key] || []).length ? '+ আরেকটা যোগ করুন' : '+ প্রথমটা যোগ করুন'"></button>
                                            </div>
                                        </template>

                                        {{-- Photos sit right next to the words they go with --}}
                                        <template x-if="isMedia(f)">
                                            <div>
                                                <input type="file" class="sr-only" :id="'file_' + f.key" accept="image/jpeg,image/png,image/webp" :multiple="f.type === 'gallery'" @change="onFiles(f, $event)">
                                                <div class="grid grid-cols-3 gap-2.5 sm:grid-cols-4">
                                                    <template x-for="(m, i) in (media[f.key] || [])" :key="m.key || m.id">
                                                        <div class="group relative">
                                                            <div class="relative aspect-square overflow-hidden rounded-xl bg-paper-2">
                                                                <img :src="m.thumb" alt="" class="h-full w-full object-cover" x-on:error="photoBroken(f, i)" :class="m.uploading || m.error ? 'opacity-50' : ''">
                                                                <div x-show="m.uploading" class="absolute inset-x-2 bottom-2 h-1.5 overflow-hidden rounded-full bg-white/70"><div class="h-full bg-kolapata transition-all" :style="`width:${m.progress}%`"></div></div>
                                                                <div x-show="m.error" class="absolute inset-0 grid place-items-center bg-sindoor/80 p-1 text-center text-xs font-semibold text-white" x-text="m.error"></div>
                                                                <button type="button" class="absolute right-1 top-1 grid h-8 w-8 place-items-center rounded-full bg-ink/75 text-white" @click="removePhoto(f, i)" aria-label="ছবি সরান">✕</button>
                                                                <div x-show="f.type === 'gallery' && (media[f.key] || []).length > 1" class="absolute bottom-1 left-1 flex gap-1">
                                                                    <button type="button" class="grid h-7 w-7 place-items-center rounded-full bg-white/85 text-xs" @click="movePhoto(f, i, -1)" aria-label="আগে">◀</button>
                                                                    <button type="button" class="grid h-7 w-7 place-items-center rounded-full bg-white/85 text-xs" @click="movePhoto(f, i, 1)" aria-label="পরে">▶</button>
                                                                </div>
                                                            </div>
                                                            <input x-show="f.type === 'gallery' && m.id" type="text" x-model="m.caption" maxlength="140" placeholder="ক্যাপশন (ঐচ্ছিক)" class="mt-1 w-full rounded-lg border-0 bg-paper px-2 py-1.5 text-xs ring-1 ring-ink/10" aria-label="ছবির ক্যাপশন">
                                                        </div>
                                                    </template>
                                                    <button type="button" @click="pick(f)" x-show="f.type === 'gallery' || !(media[f.key] || []).length"
                                                            class="flex aspect-square flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-ink/20 bg-paper text-ink-soft transition hover:border-sindoor hover:text-sindoor">
                                                        <x-icon name="upload" class="h-7 w-7" />
                                                        <span class="text-xs font-semibold" x-text="f.type === 'image' ? 'ছবি দিন' : 'ছবি যোগ করুন'"></span>
                                                    </button>
                                                </div>
                                                <p class="mt-2 text-xs text-ink-mute" x-show="f.type === 'gallery'">একসাথে অনেকগুলো ছবি বেছে নিতে পারবেন। মোট <span x-text="bn(photoCount) + '/' + bn(photoLimit)"></span>টি · প্রতিটি সর্বোচ্চ <span x-text="bn(boot.limits.max_mb)"></span> MB</p>
                                            </div>
                                        </template>

                                        <p class="field-help" :id="'h_' + f.key" x-show="f.help" x-text="f.help"></p>
                                        <p class="field-error" x-show="fieldError(f.key) || errors['media.' + f.key]" x-text="fieldError(f.key) || errors['media.' + f.key]"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Live preview: beside the form on large screens, a full-screen sheet on phones --}}
                <div x-show="step === 3" :class="previewOpen ? 'fixed inset-0 z-[70] flex flex-col bg-ink px-3 pb-3 pt-3' : 'hidden lg:sticky lg:top-[8.75rem] lg:block'" @keydown.escape.window="previewOpen = false"
                     :role="previewOpen ? 'dialog' : null" :aria-modal="previewOpen ? 'true' : null" aria-label="আপনার পেজের লাইভ প্রিভিউ">
                    <div class="mb-2 flex items-center justify-between gap-3" :class="previewOpen ? 'text-paper' : ''">
                        <div>
                            <p class="font-bold"><span class="mr-1 inline-block h-2.5 w-2.5 animate-pulse rounded-full bg-kolapata"></span> লাইভ প্রিভিউ</p>
                            <p class="text-xs" :class="previewOpen ? 'text-paper/70' : 'text-ink-mute'"><b class="text-sindoor">লাল দাগ</b> = এই লেখাটা যেখানে বসবে · ঝাপসা লেখা = নমুনা</p>
                        </div>
                        <button type="button" x-show="previewOpen" class="btn btn-primary btn-sm shrink-0" @click="previewOpen = false">ঠিক আছে, লিখি</button>
                    </div>
                    <div class="relative mx-auto w-full overflow-hidden rounded-[1.8rem] border-[7px] border-ink bg-white shadow-[var(--shadow-lift)]" :class="previewOpen ? 'max-w-[430px] flex-1' : 'h-[calc(100vh-15.5rem)] min-h-[420px] max-h-[760px]'">
                        <div x-show="liveLoading" class="absolute right-3 top-3 z-10 rounded-full bg-ink/80 px-3 py-1 text-xs font-semibold text-paper">আপডেট হচ্ছে…</div>
                        <iframe x-ref="live" title="আপনার পেজের লাইভ প্রিভিউ" class="block h-full w-full bg-white"></iframe>
                    </div>
                </div>
            </div>

            <button type="button" x-show="step === 3 && !previewOpen" @click="openPreview()"
                    class="fixed bottom-[5.6rem] right-4 z-40 inline-flex items-center gap-2 rounded-full bg-ink px-4 py-3 text-sm font-bold text-paper shadow-[var(--shadow-lift)] lg:hidden">
                👀 পেজটা কেমন হচ্ছে?
            </button>
        </section>

        {{-- STEP 4: Link and contact --}}
        <section x-show="step === 4" x-transition.opacity.duration.300ms class="space-y-6">
            <div class="card space-y-5 p-5 sm:p-7">
                <div>
                    <h2 class="text-xl font-bold">পেজের লিংক</h2>
                    <p class="text-sm text-ink-soft">পছন্দের নাম দিন। খালি থাকলে আমরা সুন্দর একটা নাম বেছে দেব।</p>
                </div>
                <div>
                    <label for="desired_slug" class="sr-only">পেজের লিংক</label>
                    <div class="flex items-stretch overflow-hidden rounded-2xl bg-white ring-1 ring-ink/15 focus-within:ring-2 focus-within:ring-nil" :class="slugState.ok === false || errors.desired_slug ? '!ring-2 !ring-sindoor' : ''">
                        <span class="flex items-center bg-paper-2 px-3 font-serif text-ink-soft">golpobari.com/</span>
                        <input id="desired_slug" class="min-w-0 flex-1 border-0 bg-transparent px-3 text-base outline-none" style="min-height:3.25rem" x-model="slug" @input="onSlugInput()" placeholder="korim" autocapitalize="off" autocomplete="off" spellcheck="false" maxlength="60">
                    </div>
                    <p class="mt-1.5 text-sm" x-show="slug" :class="slugState.ok ? 'text-kolapata' : slugState.ok === false ? 'text-sindoor-dark' : 'text-ink-mute'" x-text="slugState.checking ? 'দেখছি…' : slugState.message"></p>
                    <p class="field-error" x-show="errors.desired_slug && !slug" x-text="errors.desired_slug"></p>
                </div>
            </div>

            <div class="card space-y-5 p-5 sm:p-7">
                <div>
                    <h2 class="text-xl font-bold">আপনার যোগাযোগ</h2>
                    <p class="text-sm text-ink-soft">পেজ তৈরি হলে এই নম্বরে জানাব। এই তথ্য পেজে দেখানো হবে না।</p>
                </div>
                <div>
                    <label class="field-label" for="customer_name">আপনার নাম <span class="text-sindoor">*</span></label>
                    <input id="customer_name" class="input" x-model="customer.name" autocomplete="name" maxlength="80" :aria-invalid="!!errors.customer_name">
                    <p class="field-error" x-show="errors.customer_name" x-text="errors.customer_name"></p>
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="field-label" for="customer_phone">মোবাইল নম্বর <span class="text-sindoor">*</span></label>
                        <input id="customer_phone" class="input" x-model="customer.phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX" maxlength="20" :aria-invalid="!!errors.customer_phone">
                        <p class="field-error" x-show="errors.customer_phone" x-text="errors.customer_phone"></p>
                    </div>
                    <div>
                        <label class="field-label" for="customer_email">ইমেইল <span class="font-normal text-ink-mute">(ঐচ্ছিক)</span></label>
                        <input id="customer_email" class="input" x-model="customer.email" type="email" inputmode="email" autocomplete="email" maxlength="120" :aria-invalid="!!errors.customer_email">
                        <p class="field-error" x-show="errors.customer_email" x-text="errors.customer_email"></p>
                    </div>
                </div>
                <div>
                    <label class="field-label" for="note">আমাদের জন্য কোনো নোট <span class="font-normal text-ink-mute">(ঐচ্ছিক)</span></label>
                    <textarea id="note" class="input !min-h-[5.5rem]" rows="3" x-model="note" maxlength="1000" placeholder="যেমন: ১৪ তারিখ রাত ১২টার আগে লাগবে"></textarea>
                </div>
            </div>
        </section>

        {{-- STEP 5: Preview --}}
        <section x-show="step === 5" x-transition.opacity.duration.300ms>
            <div class="mb-4 flex items-center justify-between gap-3">
                <p class="text-sm text-ink-soft">👆 ছুঁয়ে দেখুন, খাম খুলবে, গান বাজবে।</p>
                <button type="button" class="shrink-0 text-sm font-semibold text-sindoor" @click="loadPreview()">↻ রিফ্রেশ</button>
            </div>
            <div class="phone mx-auto w-full max-w-[390px]" style="height: min(760px, 74dvh)">
                <div class="phone__screen">
                    <div x-show="previewLoading" class="grid h-full place-items-center text-ink-mute">প্রিভিউ তৈরি হচ্ছে…</div>
                    <iframe x-show="!previewLoading" :srcdoc="previewHtml" title="আপনার পেজের প্রিভিউ" allow="autoplay" class="block h-full w-full"></iframe>
                </div>
            </div>
            <p class="mt-4 text-center text-sm text-ink-soft">চূড়ান্ত পেজে আমরা লেখা আর ছবি আরও যত্ন করে সাজাব। কিছু বদলাতে চাইলে পেছনে যান।</p>
        </section>

        {{-- STEP 6: Package --}}
        <section x-show="step === 6" x-transition.opacity.duration.300ms class="space-y-3">
            <template x-for="p in boot.packages" :key="p.slug">
                <button type="button" @click="choosePackage(p.slug)" :aria-pressed="packageSlug === p.slug" :disabled="photoCount > packageLimit(p)"
                        class="card relative flex w-full items-center gap-4 p-5 text-left transition disabled:opacity-50" :class="packageSlug === p.slug ? 'ring-[3px] ring-ink' : ''">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full ring-2" :class="packageSlug === p.slug ? 'bg-ink ring-ink' : 'ring-ink/25'">
                        <span x-show="packageSlug === p.slug" class="h-2.5 w-2.5 rounded-full bg-paper"></span>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex flex-wrap items-center gap-2">
                            <span class="text-xl font-bold" x-text="p.name"></span>
                            <span x-show="p.featured" class="rounded-md bg-holud px-2 py-0.5 text-xs font-bold">জনপ্রিয়</span>
                        </span>
                        <span class="block text-sm text-ink-soft" x-text="p.description"></span>
                        <span class="block text-xs text-ink-mute" x-text="'সর্বোচ্চ ' + bn(packageLimit(p)) + 'টি ছবি' + (photoCount > packageLimit(p) ? ' · আপনার ছবি বেশি' : '')"></span>
                    </span>
                    <span class="text-right">
                        <span class="block font-display text-2xl font-extrabold" x-text="p.price_label"></span>
                        <s class="text-sm text-ink-mute" x-show="p.compare_label" x-text="p.compare_label"></s>
                    </span>
                </button>
            </template>
        </section>

        {{-- STEP 7: Coupon --}}
        <section x-show="step === 7" x-transition.opacity.duration.300ms>
            <div class="card p-5 sm:p-7">
                <label class="field-label" for="coupon">কুপন কোড</label>
                <div class="flex gap-2">
                    <input id="coupon" class="input uppercase" x-model="coupon.code" :disabled="!!coupon.applied" placeholder="WELCOME20" autocapitalize="characters" autocomplete="off" maxlength="40" @keydown.enter.prevent="applyCoupon()">
                    <button type="button" class="btn btn-ink shrink-0" x-show="!coupon.applied" @click="applyCoupon()" :disabled="coupon.loading"><span x-text="coupon.loading ? '…' : 'প্রয়োগ'"></span></button>
                    <button type="button" class="btn btn-ghost shrink-0" x-show="coupon.applied" @click="removeCoupon()">সরান</button>
                </div>
                <p class="mt-2 text-sm font-medium" x-show="coupon.message" :class="coupon.error ? 'text-sindoor-dark' : 'text-kolapata'" x-text="coupon.message"></p>
                <p class="field-error" x-show="errors.coupon_code" x-text="errors.coupon_code"></p>

                <dl class="mt-6 space-y-2 border-t-2 border-dashed border-ink/10 pt-5">
                    <div class="flex justify-between"><dt class="text-ink-soft">প্যাকেজ (<span x-text="package?.name"></span>)</dt><dd x-text="money(totals.original)"></dd></div>
                    <div class="flex justify-between text-kolapata" x-show="totals.discount > 0"><dt>ছাড়</dt><dd x-text="'−' + money(totals.discount)"></dd></div>
                    <div class="flex justify-between text-xl font-bold"><dt>মোট</dt><dd x-text="money(totals.final)"></dd></div>
                </dl>
            </div>
        </section>

        {{-- STEP 8: Payment instructions --}}
        <section x-show="step === 8" x-transition.opacity.duration.300ms>
            <div class="overflow-hidden rounded-[var(--radius-card)] bg-[#e2136e] text-white shadow-[var(--shadow-lift)]">
                <div class="flex items-center justify-between px-5 pt-5 sm:px-7">
                    <p class="text-lg font-bold tracking-wide">bKash · Send Money</p>
                    <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold" x-text="boot.bkash.type"></span>
                </div>
                <div class="px-5 pb-6 pt-4 sm:px-7">
                    <p class="text-sm text-white/80">এই নম্বরে পাঠান</p>
                    <div class="mt-1 flex items-center justify-between gap-3" x-data="copyText(boot.bkash.number)">
                        <p class="font-serif text-[2rem] font-bold tracking-wider sm:text-4xl" x-text="boot.bkash.number || '—'"></p>
                        <button type="button" class="btn btn-sm shrink-0 bg-white text-[#b30f57]" @click="copy()" x-show="boot.bkash.number"><span x-text="copied ? 'কপি হয়েছে ✓' : 'কপি'"></span></button>
                    </div>
                    <div class="mt-4 flex items-center justify-between gap-3 rounded-2xl bg-white/15 px-4 py-3">
                        <div><p class="text-sm text-white/80">ঠিক এই পরিমাণ পাঠান</p><p class="text-2xl font-extrabold" x-text="money(totals.final)"></p></div>
                    </div>
                </div>
            </div>
            <ol class="card mt-5 space-y-4 p-5 sm:p-7">
                @foreach ([
                    ['bKash অ্যাপ খুলুন', 'অথবা *247# ডায়াল করুন।'],
                    ['"Send Money" বেছে নিন', 'উপরের নম্বরটি দিন।'],
                    ['ঠিক পরিমাণ টাকা দিন', 'উপরে দেখানো মোট টাকা।'],
                    ['Transaction ID কপি করুন', 'পেমেন্টের পর SMS বা অ্যাপে পাবেন (যেমন 9BK7A1B2C3)।'],
                    ['পরের ধাপে তথ্যটি দিন', 'আমরা যাচাই করে কাজ শুরু করব।'],
                ] as $i => [$t, $s])
                    <li class="flex gap-4">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-[#e2136e]/10 font-bold text-[#b30f57]">{{ \App\Support\Bn::digits($i + 1) }}</span>
                        <span><span class="block font-semibold">{{ $t }}</span><span class="text-sm text-ink-soft">{{ $s }}</span></span>
                    </li>
                @endforeach
            </ol>
            <p class="mt-4 whitespace-pre-line rounded-2xl bg-white px-4 py-3 text-sm text-ink-soft ring-1 ring-ink/10" x-show="boot.bkash.instructions" x-text="boot.bkash.instructions"></p>
        </section>

        {{-- STEP 9: bKash details --}}
        <section x-show="step === 9" x-transition.opacity.duration.300ms>
            <div class="card space-y-5 p-5 sm:p-7">
                <div>
                    <label class="field-label" for="payment_sender">যে bKash নম্বর থেকে পাঠিয়েছেন <span class="text-sindoor">*</span></label>
                    <input id="payment_sender" class="input" x-model="payment.sender" type="tel" inputmode="tel" placeholder="01XXXXXXXXX" maxlength="20" autocomplete="off" :aria-invalid="!!errors.payment_sender">
                    <p class="field-error" x-show="errors.payment_sender" x-text="errors.payment_sender"></p>
                </div>
                <div>
                    <label class="field-label" for="payment_trx">Transaction ID <span class="text-sindoor">*</span></label>
                    <input id="payment_trx" class="input font-mono uppercase tracking-wider" x-model="payment.trx" placeholder="9BK7A1B2C3" maxlength="20" autocapitalize="characters" autocomplete="off" spellcheck="false" :aria-invalid="!!errors.payment_trx">
                    <p class="field-error" x-show="errors.payment_trx" x-text="errors.payment_trx"></p>
                </div>
                <div>
                    <label class="field-label" for="payment_amount">কত টাকা পাঠিয়েছেন <span class="text-sindoor">*</span></label>
                    <input id="payment_amount" class="input" x-model="payment.amount" type="number" inputmode="decimal" min="1" step="0.01" :aria-invalid="!!errors.payment_amount">
                    <p class="field-help">মোট: <b x-text="money(totals.final)"></b></p>
                    <p class="field-error" x-show="errors.payment_amount" x-text="errors.payment_amount"></p>
                </div>
                <p class="rounded-2xl bg-holud/20 px-4 py-3 text-sm font-medium text-ink">⚠️ ভুল Transaction ID দিলে order verification দেরি হতে পারে।</p>
                <p class="text-xs text-ink-mute">নিরাপত্তার জন্য পেমেন্টের তথ্য আপনার ব্রাউজারে সংরক্ষণ করা হয় না।</p>
            </div>
        </section>

        {{-- STEP 10: Review --}}
        <section x-show="step === 10" x-transition.opacity.duration.300ms class="space-y-4">
            <div class="card divide-y divide-dashed divide-ink/10 px-5 sm:px-7">
                <template x-for="row in [
                    ['ক্যাটাগরি', category?.name, 1], ['টেমপ্লেট', template?.name, 2], ['উপলক্ষ', occasion, 2],
                    ['লিংক', slug ? 'golpobari.com/' + slug : 'আমরা বেছে দেব', 4], ['প্যাকেজ', package ? package.name + ' (' + package.duration + ')' : '', 6],
                    ['দাম', money(totals.original), 6], ['কুপন', coupon.applied ? coupon.applied.code : '—', 7]
                ]" :key="row[0]">
                    <div class="flex items-center justify-between gap-4 py-3.5" x-show="row[1]">
                        <span class="text-ink-soft" x-text="row[0]"></span>
                        <span class="flex items-center gap-3 text-right font-semibold"><span x-text="row[1]"></span>
                            <button type="button" class="text-xs font-semibold text-sindoor underline" @click="goTo(row[2])">বদলান</button></span>
                    </div>
                </template>
                <div class="flex justify-between py-3.5 text-kolapata" x-show="totals.discount > 0"><span>ছাড়</span><span class="font-semibold" x-text="'−' + money(totals.discount)"></span></div>
                <div class="flex justify-between py-4 text-xl font-extrabold"><span>মোট</span><span x-text="money(totals.final)"></span></div>
            </div>

            <div class="card px-5 py-4 sm:px-7">
                <div class="flex items-center justify-between"><h2 class="text-lg font-bold">পেমেন্ট</h2><button type="button" class="text-xs font-semibold text-sindoor underline" @click="goTo(9)">বদলান</button></div>
                <p class="mt-1 text-ink-soft">bKash · <span x-text="payment.sender"></span> · <span class="font-mono" x-text="payment.trx.toUpperCase()"></span> · <span x-text="money(payment.amount || 0)"></span></p>
                <p class="mt-1 text-sm font-semibold text-sindoor-dark" x-show="Number(payment.amount) !== Number(totals.final)">পাঠানো টাকা মোটের সাথে মিলছে না। ঠিক থাকলে এগিয়ে যান, আমরা যাচাই করে নেব।</p>
            </div>

            <div class="card px-5 py-4 sm:px-7">
                <div class="flex items-center justify-between"><h2 class="text-lg font-bold">আপনার তথ্য</h2><button type="button" class="text-xs font-semibold text-sindoor underline" @click="goTo(3)">গল্প বদলান</button></div>
                <p class="mt-1 text-ink-soft"><button type="button" class="float-right text-xs font-semibold text-sindoor underline" @click="goTo(4)">যোগাযোগ বদলান</button><span x-text="customer.name"></span> · <span x-text="customer.phone"></span><span x-show="customer.email" x-text="' · ' + customer.email"></span></p>
                <dl class="mt-3 space-y-2 text-sm">
                    <template x-for="f in textFields" :key="f.key">
                        <div x-show="f.type === 'repeater' ? (content[f.key] || []).length : content[f.key]">
                            <dt class="font-semibold text-ink" x-text="f.label"></dt>
                            <dd class="line-clamp-3 whitespace-pre-line text-ink-soft" x-text="f.type === 'repeater' ? bn((content[f.key] || []).length) + 'টি আইটেম' : (f.type === 'music' ? musicLabel(content[f.key]) : content[f.key])"></dd>
                        </div>
                    </template>
                </dl>
            </div>

            <div class="card px-5 py-4 sm:px-7" x-show="photoCount">
                <div class="flex items-center justify-between"><h2 class="text-lg font-bold" x-text="'ছবি (' + bn(photoCount) + ')'"></h2><button type="button" class="text-xs font-semibold text-sindoor underline" @click="goTo(3)">বদলান</button></div>
                <div class="mt-3 grid grid-cols-5 gap-1.5 sm:grid-cols-8">
                    <template x-for="m in Object.values(media).flat().filter(x => x.id)" :key="m.id"><img :src="m.thumb" alt="" class="aspect-square rounded-lg object-cover"></template>
                </div>
            </div>

            <label class="flex cursor-pointer items-start gap-3 rounded-[var(--radius-card)] bg-white p-5 ring-1 ring-ink/10 sm:px-7">
                <input type="checkbox" x-model="confirm" class="mt-1 h-6 w-6 shrink-0 accent-[#d9482b]">
                <span><span class="block text-lg font-bold">সব তথ্য ঠিক আছে</span>
                    <span class="text-sm text-ink-soft">আমি <a :href="boot.urls.terms" target="_blank" class="underline">শর্তাবলি</a> মেনে নিচ্ছি এবং ছবি ও লেখা ব্যবহারের অধিকার আমার আছে।</span></span>
            </label>
        </section>

        <p class="js-step-error mt-5 rounded-2xl bg-sindoor/10 px-4 py-3 font-medium text-sindoor-dark" x-show="stepError" x-text="stepError" role="alert"></p>
    </div>

    {{-- Bottom action bar: thumb-reachable on phones --}}
    <div class="fixed inset-x-0 bottom-0 z-40 border-t border-ink/10 bg-paper/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 backdrop-blur">
        <div class="mx-auto flex max-w-3xl items-center gap-3">
            <button type="button" class="btn btn-ghost w-14 shrink-0 !px-0" x-show="step > 1" @click="back()" aria-label="আগের ধাপ"><x-icon name="chevron-left" class="h-6 w-6" /></button>
            <template x-if="step < 10">
                <button type="button" class="btn btn-primary flex-1 text-lg" @click="next()" x-show="step !== 1 && !(step === 2 && !templateSlug)">
                    <span x-text="{3: 'লেখা শেষ, এগোই', 4: 'পুরো প্রিভিউ দেখি', 5: 'ঠিক আছে, এগোই', 6: 'এগিয়ে যান', 7: coupon.applied ? 'এগিয়ে যান' : 'কুপন নেই, এগোই', 8: 'টাকা পাঠিয়েছি', 9: 'রিভিউ করি'}[step] || 'এগিয়ে যান'"></span>
                    <x-icon name="arrow-right" class="h-5 w-5" />
                </button>
            </template>
            <template x-if="step === 1 || (step === 2 && !templateSlug)">
                <p class="flex-1 text-center text-sm text-ink-mute">উপরে থেকে একটি বেছে নিন</p>
            </template>
            <template x-if="step === 10">
                <button type="button" class="btn btn-primary flex-1 text-lg" @click="submit()" :disabled="submitting || !confirm" :aria-disabled="submitting || !confirm">
                    <span x-text="submitting ? 'জমা হচ্ছে…' : 'অর্ডার জমা দিন'"></span>
                </button>
            </template>
        </div>
    </div>

    {{-- Template preview modal: the real page in a phone --}}
    <div x-show="previewModal" x-transition.opacity class="fixed inset-0 z-[60] flex flex-col items-center justify-center bg-night/85 backdrop-blur sm:p-6" @keydown.escape.window="previewModal = null" @click.self="previewModal = null" role="dialog" aria-modal="true">
        <div class="flex w-full max-w-[420px] items-center justify-between gap-3 bg-night px-4 py-3 text-paper sm:mb-3 sm:bg-transparent sm:px-1 sm:py-0">
            <p class="min-w-0 truncate font-semibold" x-text="previewModal?.name"></p>
            <div class="flex shrink-0 gap-2">
                <button type="button" class="btn btn-gold btn-sm" @click="selectTemplate(previewModal.slug); previewModal = null">এটা নেব</button>
                <button type="button" class="grid h-10 w-10 place-items-center rounded-full bg-white/10" @click="previewModal = null" aria-label="বন্ধ করুন"><x-icon name="x" class="h-5 w-5" /></button>
            </div>
        </div>
        <div class="pv-frame w-full flex-1 sm:w-auto sm:flex-none">
            <div class="pv-screen h-full bg-white">
                <template x-if="previewModal"><iframe :src="previewModal.render" allow="autoplay" class="block h-full w-full border-0" :title="previewModal.name + ' প্রিভিউ'"></iframe></template>
            </div>
        </div>
    </div>
</div>
@endsection
