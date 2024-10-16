<x-filament-panels::page
        @class([
            'fi-resource-create-record-page',
            'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
        ])
>
    <?php
    print_r($errors);
    ?>
    <div
            x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('laravel-migrator'))]"

            x-data='{
            activeTab: "initialize",
            connections: @json($connections),
            sourceType: null,
            sourceValue: null,
            destinationValue: null,
            setSource: function(type, value) {
                this.sourceType = type;
                this.sourceValue = value;
                console.log(type);
                console.log(value);
            },
            init: function() {
                $watch("sourceType", function(value) { $wire.set("data.type", value) });
                $watch("sourceValue", function(value) { $wire.set("data.source", value) });
                $watch("destinationValue", function(value) { $wire.set("data.destination", value) });
            }
        }'>
        <form wire:submit.prevent="create">

        <div class="container mx-auto w-full max-w-lg bg-white p-4">
        <div class="" x-bind:class="activeTab == 'initialize' ? '' : 'hidden'">
            <p class="text-center py-4">How would you like to start the import process?</p>
            <div class="grid grid-cols-4 gap-x-3 items-stretch m-auto">
                <x-filament::button
                        wire:model.defer="data.type"
                        @click="activeTab = 'connectionPicker'; sourceType = 'connection';" :disabled="empty($connections)">
                    Use a connection
                </x-filament::button>
                <x-filament::button color="gray"
                                    wire:model.defer="data.type"
                                    :disabled="true">
                    Use an existing file
                </x-filament::button>
                <x-filament::button
                        wire:model.defer="data.type"
                        color="gray" :disabled="true">
                    Upload a file
                </x-filament::button>
                <x-filament::button
                        wire:model.defer="data.type"
                        color="gray" :disabled="true">
                    Download a file
                </x-filament::button>
            </div>
        </div>
            <div class="" x-bind:class="activeTab === 'connectionPicker' ? '' : 'hidden'">
                <p class="text-center py-4">Select a remote data connection</p>
                <div class="rounded shadow bg-white overflow-hidden peer-checked:flex flex-col w-full mt-1 mb-4 border border-gray-200">
                    <template x-for="(name, index)  in connections">
                        <div class="cursor-pointer group" :class="[index ? 'border-t' : '', sourceValue == name ? 'bg-gray-100' : '']">
                            <div class="flex items-center justify-start gap-2 font-medium text-slate-700 has-[:disabled]:opacity-75 dark:text-slate-300">
                                <input :id="'connection_remote_' + index" type="radio" class="hidden" name="sourceValue" :value="name" x-model="sourceValue"
                                       wire:model.defer="data.source"
                                >
                                <label :for="'connection_remote_' + index"
                                       class="lock p-2 border-l-4 group-hover:border-blue-600 group-hover:bg-gray-100 w-full text-left outline-none transition duration-75 hover:bg-gray-100"
                                       :class="sourceValue == name ? 'border-blue-600' : 'border-transparent'"
                                       x-text="name">
                                </label>
                            </div>
                    </template>

                </div>

                <p class="text-center py-4" :class="sourceValue ? '' : 'hidden'">Select a local data connection</p>
                <div class="rounded shadow bg-white overflow-hidden peer-checked:flex flex-col w-full mt-1 border border-gray-200"
                     :class="sourceValue ? '' : 'hidden'">
                    <template x-for="(name, index)  in connections">
                        <div class="cursor-pointer group" :class="[index ? 'border-t' : '', destinationValue == name ? 'bg-gray-100' : '', sourceValue == name ? 'hidden' : '']">
                            <div class="flex items-center justify-start gap-2 font-medium text-slate-700 has-[:disabled]:opacity-75 dark:text-slate-300">
                                <input :id="'connection_destination_' + index" type="radio" class="hidden" name="destinationValue" :value="name"
                                       x-model="destinationValue"
                                >
                                <label :for="'connection_destination_' + index"
                                       class="lock p-2 border-l-4 group-hover:border-blue-600 group-hover:bg-gray-100 w-full text-left outline-none transition duration-75 hover:bg-gray-100"
                                       :class="destinationValue == name ? 'border-blue-600' : 'border-transparent'"
                                       x-text="name">
                                </label>
                            </div>
                    </template>

                </div>

                <div class="grid grid-cols-2 gap-x-3 items-stretch m-auto mt-4"
                :class="sourceValue && destinationValue ? '' : 'hidden'"
                >
                <x-filament::button type="button" @click="sourceValue = null; destinationValue = null; sourceType = null; activeTab = 'initialize'"
                color="info">
                    Back
                </x-filament::button>
                <x-filament::button type="submit">
                    Continue
                </x-filament::button>
                </div>
		        <?php
		        ?>
            </div>

        </div>
        <!-- Author: FormBold Team -->
        <!-- Learn More: https://formbold.com -->
        <div class="mx-auto w-full max-w-[550px] bg-white p-4" x-bind:class="activeTab === 'stepO2ne' ? '' : 'hidden'">
            <form
                    class="py-6 px-9"
                    action="https://formbold.com/s/FORM_ID"
                    method="POST"
            >
                <div class="mb-5">
                    <label
                            for="email"
                            class="mb-3 block text-base font-medium text-[#07074D]"
                    >
                        Send files to this email:
                    </label>
                    <input
                            type="email"
                            name="email"
                            id="email"
                            placeholder="example@domain.com"
                            class="w-full rounded-md border border-[#e0e0e0] bg-white py-3 px-6 text-base font-medium text-[#6B7280] outline-none focus:border-[#6A64F1] focus:shadow-md"
                    />
                </div>

                <div class="mb-6 pt-4">
                    <label class="mb-5 block text-xl font-semibold text-[#07074D]">
                        Upload File
                    </label>

                    <div class="mb-8">
                        <input type="file" name="file" id="file" class="sr-only" />
                        <label
                                for="file"
                                class="relative flex min-h-[200px] items-center justify-center rounded-md border border-dashed border-[#e0e0e0] p-12 text-center"
                        >
                            <div>
              <span class="mb-2 block text-xl font-semibold text-[#07074D]">
                Drop files here
              </span>
                                <span class="mb-2 block text-base font-medium text-[#6B7280]">
                Or
              </span>
                                <span
                                        class="inline-flex rounded border border-[#e0e0e0] py-2 px-7 text-base font-medium text-[#07074D]"
                                >
                Browse
              </span>
                            </div>
                        </label>
                    </div>

                    <div class="mb-5 rounded-md bg-[#F5F7FB] py-4 px-8">
                        <div class="flex items-center justify-between">
            <span class="truncate pr-3 text-base font-medium text-[#07074D]">
              banner-design.png
            </span>
                            <button class="text-[#07074D]">
                                <svg
                                        width="10"
                                        height="10"
                                        viewBox="0 0 10 10"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                            fill-rule="evenodd"
                                            clip-rule="evenodd"
                                            d="M0.279337 0.279338C0.651787 -0.0931121 1.25565 -0.0931121 1.6281 0.279338L9.72066 8.3719C10.0931 8.74435 10.0931 9.34821 9.72066 9.72066C9.34821 10.0931 8.74435 10.0931 8.3719 9.72066L0.279337 1.6281C-0.0931125 1.25565 -0.0931125 0.651788 0.279337 0.279338Z"
                                            fill="currentColor"
                                    />
                                    <path
                                            fill-rule="evenodd"
                                            clip-rule="evenodd"
                                            d="M0.279337 9.72066C-0.0931125 9.34821 -0.0931125 8.74435 0.279337 8.3719L8.3719 0.279338C8.74435 -0.0931127 9.34821 -0.0931123 9.72066 0.279338C10.0931 0.651787 10.0931 1.25565 9.72066 1.6281L1.6281 9.72066C1.25565 10.0931 0.651787 10.0931 0.279337 9.72066Z"
                                            fill="currentColor"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="rounded-md bg-[#F5F7FB] py-4 px-8">
                        <div class="flex items-center justify-between">
            <span class="truncate pr-3 text-base font-medium text-[#07074D]">
              banner-design.png
            </span>
                            <button class="text-[#07074D]">
                                <svg
                                        width="10"
                                        height="10"
                                        viewBox="0 0 10 10"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                            fill-rule="evenodd"
                                            clip-rule="evenodd"
                                            d="M0.279337 0.279338C0.651787 -0.0931121 1.25565 -0.0931121 1.6281 0.279338L9.72066 8.3719C10.0931 8.74435 10.0931 9.34821 9.72066 9.72066C9.34821 10.0931 8.74435 10.0931 8.3719 9.72066L0.279337 1.6281C-0.0931125 1.25565 -0.0931125 0.651788 0.279337 0.279338Z"
                                            fill="currentColor"
                                    />
                                    <path
                                            fill-rule="evenodd"
                                            clip-rule="evenodd"
                                            d="M0.279337 9.72066C-0.0931125 9.34821 -0.0931125 8.74435 0.279337 8.3719L8.3719 0.279338C8.74435 -0.0931127 9.34821 -0.0931123 9.72066 0.279338C10.0931 0.651787 10.0931 1.25565 9.72066 1.6281L1.6281 9.72066C1.25565 10.0931 0.651787 10.0931 0.279337 9.72066Z"
                                            fill="currentColor"
                                    />
                                </svg>
                            </button>
                        </div>
                        <div class="relative mt-5 h-[6px] w-full rounded-lg bg-[#E2E5EF]">
                            <div
                                    class="absolute left-0 right-0 h-full w-[75%] rounded-lg bg-[#6A64F1]"
                            ></div>
                        </div>
                    </div>
                </div>

                <div>
                    <button
                            class="hover:shadow-form w-full rounded-md bg-[#6A64F1] py-3 px-8 text-center text-base font-semibold text-white outline-none"
                    >
                        Send File
                    </button>
                </div>
            </form>
        </div>
        </form>


    </div>
</x-filament-panels::page>
