<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    {{ __('Onboard New Customer') }}
                </h2>
                <div class="flex items-center gap-2 mt-1 text-sm text-bankos-text-sec">
                    <a href="{{ route('customers.index') }}" class="hover:text-bankos-primary">Customers</a>
                    <span>/</span>
                    <span class="text-bankos-text dark:text-white font-medium">New</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="card p-0 overflow-hidden shadow-lg border-0 ring-1 ring-bankos-border dark:ring-bankos-dark-border">
            
            <!-- Progress Bar (Visual Only for Demo) -->
            <div class="bg-gray-50 dark:bg-bankos-dark-bg/50 px-8 py-5 border-b border-bankos-border dark:border-bankos-dark-border relative overflow-hidden">
                <div class="absolute bottom-0 left-0 h-1 bg-bankos-primary w-1/3"></div>
                <div class="flex justify-between items-center relative z-10">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-bankos-primary text-white flex items-center justify-center font-bold text-sm">1</div>
                        <div>
                            <p class="font-bold text-bankos-primary">Bio Data</p>
                            <p class="text-xs text-bankos-muted">Basic Identity Info</p>
                        </div>
                    </div>
                    
                    <div class="w-16 h-px bg-gray-300 dark:bg-gray-700 hidden sm:block"></div>
                    
                    <div class="flex items-center gap-3 opacity-50">
                        <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-bankos-muted flex items-center justify-center font-bold text-sm">2</div>
                        <div class="hidden sm:block">
                            <p class="font-bold text-bankos-text dark:text-white">KYC & Address</p>
                            <p class="text-xs text-bankos-muted">Compliance Documents</p>
                        </div>
                    </div>

                    <div class="w-16 h-px bg-gray-300 dark:bg-gray-700 hidden sm:block"></div>
                    
                    <div class="flex items-center gap-3 opacity-50">
                        <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-bankos-muted flex items-center justify-center font-bold text-sm">3</div>
                        <div class="hidden sm:block">
                            <p class="font-bold text-bankos-text dark:text-white">Review</p>
                            <p class="text-xs text-bankos-muted">Final Confirmation</p>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('customers.store') }}" method="POST" class="p-8">
                @csrf
                
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 dark:bg-red-900/20 text-red-600 p-4 rounded-lg flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <div>
                            <h4 class="font-bold text-sm">Validation Error</h4>
                            <ul class="text-xs list-disc list-inside mt-1 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="col-span-1 md:col-span-2 space-y-2">
                        <h3 class="font-bold text-lg text-bankos-text dark:text-white pb-2 border-b border-bankos-border dark:border-bankos-dark-border">Personal Information</h3>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Customer Type <span class="text-red-500">*</span></label>
                        <select name="type" class="form-select w-full" required>
                            <option value="individual" {{ old('type') == 'individual' ? 'selected' : '' }}>Individual</option>
                            <option value="corporate" {{ old('type') == 'corporate' ? 'selected' : '' }}>Corporate (Business)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Gender <span class="text-red-500">*</span></label>
                        <select name="gender" class="form-select w-full" required>
                            <option value="">Select Gender...</option>
                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" class="form-input w-full" placeholder="John" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Last Name (Surname) <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" class="form-input w-full" placeholder="Doe" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Middle Name</label>
                        <input type="text" name="middle_name" value="{{ old('middle_name') }}" class="form-input w-full" placeholder="Optional">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Date of Birth <span class="text-red-500">*</span></label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="form-input w-full" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Marital Status <span class="text-red-500">*</span></label>
                        <select name="marital_status" class="form-select w-full" required>
                            <option value="">Select Status...</option>
                            <option value="single" {{ old('marital_status') == 'single' ? 'selected' : '' }}>Single</option>
                            <option value="married" {{ old('marital_status') == 'married' ? 'selected' : '' }}>Married</option>
                            <option value="divorced" {{ old('marital_status') == 'divorced' ? 'selected' : '' }}>Divorced</option>
                            <option value="widowed" {{ old('marital_status') == 'widowed' ? 'selected' : '' }}>Widowed</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Occupation <span class="text-red-500">*</span></label>
                        <input type="text" name="occupation" value="{{ old('occupation') }}" class="form-input w-full" placeholder="e.g. Software Engineer" required>
                    </div>

                    <div class="col-span-1 md:col-span-2 space-y-2 mt-4">
                        <h3 class="font-bold text-lg text-bankos-text dark:text-white pb-2 border-b border-bankos-border dark:border-bankos-dark-border">Contact & Address</h3>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Phone Number <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-input w-full" placeholder="+234..." required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-input w-full" placeholder="john.doe@example.com">
                    </div>

                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Street Address <span class="text-red-500">*</span></label>
                        <input type="text" name="address_street" value="{{ old('address_street') }}" class="form-input w-full" placeholder="123 Bank Way, Suite 4B" required>
                    </div>

                    <!-- Alpine.js block for State/LGA -->
                    <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6" x-data="nigerianStates()">
                        <div>
                            <label class="block text-sm font-medium text-bankos-text-sec mb-2">State <span class="text-red-500">*</span></label>
                            <select name="address_state" x-model="selectedState" @change="updateLgas" class="form-select w-full" required>
                                <option value="">Select State...</option>
                                <template x-for="state in states" :key="state.name">
                                    <option :value="state.name" x-text="state.name"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-bankos-text-sec mb-2">Local Government Area <span class="text-red-500">*</span></label>
                            <select name="address_lga" x-model="selectedLga" class="form-select w-full" required :disabled="lgas.length === 0">
                                <option value="">Select LGA...</option>
                                <template x-for="lga in lgas" :key="lga">
                                    <option :value="lga" x-text="lga"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="col-span-1 md:col-span-2 space-y-2 mt-4">
                        <h3 class="font-bold text-lg text-bankos-text dark:text-white pb-2 border-b border-bankos-border dark:border-bankos-dark-border">Identity / BVN</h3>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Bank Verification Number (BVN)</label>
                        <input type="text" name="bvn" value="{{ old('bvn') }}" class="form-input w-full" placeholder="11-digit BVN" maxlength="11">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">National Identity Number (NIN)</label>
                        <input type="text" name="nin" value="{{ old('nin') }}" class="form-input w-full" placeholder="11-digit NIN" maxlength="11">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4 pt-6 border-t border-bankos-border dark:border-bankos-dark-border">
                    <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary flex items-center gap-2">
                        Save & Proceed to KYC
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Alpine State/LGA Data Script -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('nigerianStates', () => ({
                states: [
                    { name: 'Abia', lgas: ['Aba North', 'Aba South', 'Arochukwu', 'Bende', 'Ikwuano', 'Isiala Ngwa North', 'Isiala Ngwa South', 'Isuikwuato', 'Obi Ngwa', 'Ohafia', 'Osisioma', 'Ugwunagbo', 'Ukwa East', 'Ukwa West', 'Umuahia North', 'Umuahia South', 'Umu Nneochi'] },
                    { name: 'Adamawa', lgas: ['Demsa', 'Fufure', 'Ganye', 'Gayuk', 'Gombi', 'Grie', 'Hong', 'Jada', 'Lamurde', 'Madagali', 'Maiha', 'Mayo Belwa', 'Michika', 'Mubi North', 'Mubi South', 'Numan', 'Shelleng', 'Song', 'Toungo', 'Yola North', 'Yola South'] },
                    { name: 'Akwa Ibom', lgas: ['Abak', 'Eastern Obolo', 'Eket', 'Esit Eket', 'Essien Udim', 'Etim Ekpo', 'Etinan', 'Ibeno', 'Ibesikpo Asutan', 'Ibiono-Ibom', 'Ika', 'Ikono', 'Ikot Abasi', 'Ikot Ekpene', 'Ini', 'Itu', 'Mbo', 'Mkpat-Enin', 'Nsit-Atai', 'Nsit-Ibom', 'Nsit-Ubium', 'Obot Akara', 'Okobo', 'Onna', 'Oron', 'Oruk Anam', 'Udung-Uko', 'Ukanafun', 'Uruan', 'Urue-Offong/Oruko', 'Uyo'] },
                    { name: 'Anambra', lgas: ['Aguata', 'Anambra East', 'Anambra West', 'Anaocha', 'Awka North', 'Awka South', 'Ayamelum', 'Dunukofia', 'Ekwusigo', 'Idemili North', 'Idemili South', 'Ihiala', 'Njikoka', 'Nnewi North', 'Nnewi South', 'Ogbaru', 'Onitsha North', 'Onitsha South', 'Orumba North', 'Orumba South', 'Oyi'] },
                    { name: 'Bauchi', lgas: ['Alkaleri', 'Bauchi', 'Bogoro', 'Damban', 'Darazo', 'Dass', 'Gamawa', 'Ganjuwa', 'Giade', 'Itas/Gadau', 'Jama\'are', 'Katagum', 'Kirfi', 'Misau', 'Ningi', 'Shira', 'Tafawa Balewa', 'Toro', 'Warji', 'Zaki'] },
                    { name: 'Bayelsa', lgas: ['Brass', 'Ekeremor', 'Kolokuma/Opokuma', 'Nembe', 'Ogbia', 'Sagbama', 'Southern Ijaw', 'Yenagoa'] },
                    { name: 'Benue', lgas: ['Agatu', 'Apa', 'Ado', 'Buruku', 'Gboko', 'Guma', 'Gwer East', 'Gwer West', 'Katsina-Ala', 'Konshisha', 'Kwande', 'Logo', 'Makurdi', 'Obi', 'Ogbadibo', 'Ohimini', 'Oju', 'Okpokwu', 'Otukpo', 'Tarka', 'Ukum', 'Ushongo', 'Vandeikya'] },
                    { name: 'Borno', lgas: ['Abadam', 'Askira/Uba', 'Bama', 'Bayo', 'Biu', 'Chibok', 'Damboa', 'Dikwa', 'Gubio', 'Guzamala', 'Gwoza', 'Hawul', 'Jere', 'Kaga', 'Kala/Balge', 'Konduga', 'Kukawa', 'Kwaya Kusar', 'Mafa', 'Magumeri', 'Maiduguri', 'Marte', 'Mobbar', 'Monguno', 'Ngala', 'Nganzai', 'Shani'] },
                    { name: 'Cross River', lgas: ['Abi', 'Akamkpa', 'Akpabuyo', 'Bakassi', 'Bekwarra', 'Biase', 'Boki', 'Calabar Municipal', 'Calabar South', 'Etung', 'Ikom', 'Obanliku', 'Obubra', 'Obudu', 'Odukpani', 'Ogoja', 'Yakuur', 'Yala'] },
                    { name: 'Delta', lgas: ['Aniocha North', 'Aniocha South', 'Bomadi', 'Burutu', 'Ethiope East', 'Ethiope West', 'Ika North East', 'Ika South', 'Isoko North', 'Isoko South', 'Ndokwa East', 'Ndokwa West', 'Okpe', 'Oshimili North', 'Oshimili South', 'Patani', 'Sapele', 'Udu', 'Ughelli North', 'Ughelli South', 'Ukwuani', 'Uvwie', 'Warri North', 'Warri South', 'Warri South West'] },
                    { name: 'Ebonyi', lgas: ['Abakaliki', 'Afikpo North', 'Afikpo South', 'Ebonyi', 'Ezza North', 'Ezza South', 'Ikwo', 'Ishielu', 'Ivo', 'Izzi', 'Ohaozara', 'Ohaukwu', 'Onicha'] },
                    { name: 'Edo', lgas: ['Akoko-Edo', 'Egor', 'Esan Central', 'Esan North-East', 'Esan South-East', 'Esan West', 'Etsako Central', 'Etsako East', 'Etsako West', 'Igueben', 'Ikpoba Okha', 'Orhionmwon', 'Oredo', 'Ovia North-East', 'Ovia South-West', 'Owan East', 'Owan West', 'Uhunmwonde'] },
                    { name: 'Ekiti', lgas: ['Ado Ekiti', 'Efon', 'Ekiti East', 'Ekiti South-West', 'Ekiti West', 'Emure', 'Gbonyin', 'Ido Osi', 'Ijero', 'Ikere', 'Ikole', 'Ilejemeje', 'Irepodun/Ifelodun', 'Ise/Orun', 'Moba', 'Oye'] },
                    { name: 'Enugu', lgas: ['Aninri', 'Awgu', 'Enugu East', 'Enugu North', 'Enugu South', 'Ezeagu', 'Igbo Etiti', 'Igbo Eze North', 'Igbo Eze South', 'Isi Uzo', 'Nkanu East', 'Nkanu West', 'Nsukka', 'Oji River', 'Udenu', 'Udi', 'Uzo Uwani'] },
                    { name: 'FCT', lgas: ['Abaji', 'Bwari', 'Gwagwalada', 'Kuje', 'Kwali', 'Municipal Area Council'] },
                    { name: 'Gombe', lgas: ['Akko', 'Balanga', 'Billiri', 'Dukku', 'Funakaye', 'Gombe', 'Kaltungo', 'Kwami', 'Nafada', 'Shongom', 'Yamaltu/Deba'] },
                    { name: 'Imo', lgas: ['Aboh Mbaise', 'Ahiazu Mbaise', 'Ehime Mbano', 'Ezinihitte', 'Ideato North', 'Ideato South', 'Ihitte/Uboma', 'Ikeduru', 'Isiala Mbano', 'Isu', 'Mbaitoli', 'Ngor Okpala', 'Njaba', 'Nkwerre', 'Nwangele', 'Obowo', 'Oguta', 'Ohaji/Egbema', 'Okigwe', 'Orlu', 'Orsu', 'Oru East', 'Oru West', 'Owerri Municipal', 'Owerri North', 'Owerri West', 'Unuimo'] },
                    { name: 'Jigawa', lgas: ['Auyo', 'Babura', 'Biriniwa', 'Birnin Kudu', 'Buji', 'Dutse', 'Gagarawa', 'Garki', 'Gumel', 'Guri', 'Gwaram', 'Gwiwa', 'Hadejia', 'Jahun', 'Kafin Hausa', 'Kaugama', 'Kazaure', 'Kiri Kasama', 'Kiyawa', 'Kaugama', 'Maigatari', 'Malam Madori', 'Miga', 'Ringim', 'Roni', 'Sule Tankarkar', 'Taura', 'Yankwashi'] },
                    { name: 'Kaduna', lgas: ['Birnin Gwari', 'Chikun', 'Giwa', 'Igabi', 'Ikara', 'Jaba', 'Jema\'a', 'Kachia', 'Kaduna North', 'Kaduna South', 'Kagarko', 'Kajuru', 'Kaura', 'Kauru', 'Kubau', 'Kudan', 'Lere', 'Makarfi', 'Sabon Gari', 'Sanga', 'Soba', 'Zangon Kataf', 'Zaria'] },
                    { name: 'Kano', lgas: ['Ajingi', 'Albasu', 'Bagwai', 'Bebeji', 'Bichi', 'Bunkure', 'Dala', 'Dambatta', 'Dawakin Kudu', 'Dawakin Tofa', 'Doguwa', 'Fagge', 'Gabasawa', 'Garko', 'Garun Mallam', 'Gaya', 'Gezawa', 'Gwale', 'Gwarzo', 'Kabo', 'Kano Municipal', 'Karaye', 'Kibiya', 'Kiru', 'Kumbotso', 'Kunchi', 'Kura', 'Madobi', 'Makoda', 'Minjibir', 'Nasarawa', 'Rano', 'Rimin Gado', 'Rogo', 'Shanono', 'Sumaila', 'Takai', 'Tarauni', 'Tofa', 'Tsanyawa', 'Tudun Wada', 'Ungogo', 'Warawa', 'Wudil'] },
                    { name: 'Katsina', lgas: ['Bakori', 'Batagarawa', 'Batsari', 'Baure', 'Bindawa', 'Charanchi', 'Dandume', 'Danja', 'Dan Musa', 'Daura', 'Dutsi', 'Dutsin Ma', 'Faskari', 'Funtua', 'Ingawa', 'Jibia', 'Kafur', 'Kaita', 'Kankara', 'Kankia', 'Katsina', 'Kurfi', 'Kusada', 'Mai\'Adua', 'Malumfashi', 'Mani', 'Mashi', 'Matazu', 'Musawa', 'Rimi', 'Sabuwa', 'Safana', 'Sandamu', 'Zango'] },
                    { name: 'Kebbi', lgas: ['Aleiro', 'Arewa Dandi', 'Argungu', 'Augie', 'Bagudo', 'Birnin Kebbi', 'Bunza', 'Dandi', 'Fakai', 'Gwandu', 'Jega', 'Kalgo', 'Koko/Besse', 'Maiyama', 'Ngaski', 'Sakaba', 'Shanga', 'Suru', 'Wasagu/Danko', 'Yauri', 'Zuru'] },
                    { name: 'Kogi', lgas: ['Adavi', 'Ajaokuta', 'Ankpa', 'Bassa', 'Dekina', 'Ibaji', 'Idah', 'Igalamela Odolu', 'Ijumu', 'Kabba/Bunu', 'Kogi', 'Lokoja', 'Mopa Muro', 'Ofu', 'Ogori/Magongo', 'Okehi', 'Okene', 'Olamaboro', 'Omala', 'Yagba East', 'Yagba West'] },
                    { name: 'Kwara', lgas: ['Asa', 'Baruten', 'Edu', 'Ekiti', 'Ifelodun', 'Ilorin East', 'Ilorin South', 'Ilorin West', 'Irepodun', 'Isin', 'Kaiama', 'Moro', 'Offa', 'Oke Ero', 'Oyun', 'Pategi'] },
                    { name: 'Lagos', lgas: ['Agege', 'Ajeromi-Ifelodun', 'Alimosho', 'Amuwo-Odofin', 'Apapa', 'Badagry', 'Epe', 'Eti Osa', 'Ibeju-Lekki', 'Ifako-Ijaiye', 'Ikeja', 'Ikorodu', 'Kosofe', 'Lagos Island', 'Lagos Mainland', 'Mushin', 'Ojo', 'Oshodi-Isolo', 'Shomolu', 'Surulere'] },
                    { name: 'Nasarawa', lgas: ['Akwanga', 'Awe', 'Doma', 'Karu', 'Keana', 'Keffi', 'Kokona', 'Lafia', 'Nasarawa', 'Nasarawa Egon', 'Obi', 'Toto', 'Wamba'] },
                    { name: 'Niger', lgas: ['Agaie', 'Agwara', 'Bida', 'Borgu', 'Bosso', 'Chanchaga', 'Edati', 'Gbako', 'Gurara', 'Katcha', 'Kontagora', 'Lapai', 'Lavun', 'Magama', 'Mariga', 'Mashegu', 'Mokwa', 'Moya', 'Paikoro', 'Rafi', 'Rijau', 'Shiroro', 'Suleja', 'Tafa', 'Wushishi'] },
                    { name: 'Ogun', lgas: ['Abeokuta North', 'Abeokuta South', 'Ado-Odo/Ota', 'Egbado North', 'Egbado South', 'Ewekoro', 'Ifo', 'Ijebu East', 'Ijebu North', 'Ijebu North East', 'Ijebu Ode', 'Ikenne', 'Imeko Afon', 'Ipokia', 'Obafemi Owode', 'Odeda', 'Odogbolu', 'Ogun Waterside', 'Remo North', 'Shagamu'] },
                    { name: 'Ondo', lgas: ['Akoko North-East', 'Akoko North-West', 'Akoko South-East', 'Akoko South-West', 'Akure North', 'Akure South', 'Ese Odo', 'Idanre', 'Ifedore', 'Ilaje', 'Ile Oluji/Okeigbo', 'Irele', 'Odigbo', 'Okitipupa', 'Ondo East', 'Ondo West', 'Ose', 'Owo'] },
                    { name: 'Osun', lgas: ['Aiyedaade', 'Aiyedire', 'Atakumosa East', 'Atakumosa West', 'Boluwaduro', 'Boripe', 'Ede North', 'Ede South', 'Egbedore', 'Ejigbo', 'Ife Central', 'Ife East', 'Ife North', 'Ife South', 'Ifedayo', 'Ifelodun', 'Ila', 'Ilesa East', 'Ilesa West', 'Irepodun', 'Irewole', 'Isokan', 'Iwo', 'Obokun', 'Odo Otin', 'Ola Oluwa', 'Olorunda', 'Oriade', 'Orolu', 'Osogbo'] },
                    { name: 'Oyo', lgas: ['Afijio', 'Akinyele', 'Atiba', 'Atisbo', 'Egbeda', 'Ibadan North', 'Ibadan North-East', 'Ibadan North-West', 'Ibadan South-East', 'Ibadan South-West', 'Ibarapa Central', 'Ibarapa East', 'Ibarapa North', 'Ido', 'Irepo', 'Iseyin', 'Itesiwaju', 'Iwajowa', 'Kajola', 'Lagelu', 'Ogbomosho North', 'Ogbomosho South', 'Ogo Oluwa', 'Olorunsogo', 'Oluyole', 'Ona Ara', 'Orelope', 'Ori Ire', 'Oyo', 'Oyo East', 'Saki East', 'Saki West', 'Surulere'] },
                    { name: 'Plateau', lgas: ['Bokkos', 'Barkin Ladi', 'Bassa', 'Jos East', 'Jos North', 'Jos South', 'Kanam', 'Kanke', 'Langtang South', 'Langtang North', 'Mangu', 'Mikang', 'Pankshin', 'Qua\'an Pan', 'Riyom', 'Shendam', 'Wase'] },
                    { name: 'Rivers', lgas: ['Abua/Odual', 'Ahoada East', 'Ahoada West', 'Akuku-Toru', 'Andoni', 'Asari-Toru', 'Bonny', 'Degema', 'Eleme', 'Emuoha', 'Etche', 'Gokana', 'Ikwerre', 'Khana', 'Obio/Akpor', 'Ogba/Egbema/Ndoni', 'Ogu/Bolo', 'Okrika', 'Omuma', 'Opobo/Nkoro', 'Oyigbo', 'Port Harcourt', 'Tai'] },
                    { name: 'Sokoto', lgas: ['Binji', 'Bodinga', 'Dange Shuni', 'Gada', 'Goronyo', 'Gudu', 'Gwadabawa', 'Illela', 'Isa', 'Kebbe', 'Kware', 'Rabah', 'Sabon Birni', 'Shagari', 'Silame', 'Sokoto North', 'Sokoto South', 'Tambuwal', 'Tangaza', 'Tureta', 'Wamako', 'Wurno', 'Yabo'] },
                    { name: 'Taraba', lgas: ['Ardo Kola', 'Bali', 'Donga', 'Gashaka', 'Gassol', 'Ibi', 'Jalingo', 'Karim Lamido', 'Kumi', 'Lau', 'Sardauna', 'Takum', 'Ussa', 'Wukari', 'Yorro', 'Zing'] },
                    { name: 'Yobe', lgas: ['Bade', 'Bursari', 'Damaturu', 'Fika', 'Fune', 'Geidam', 'Gujba', 'Gulani', 'Jakusko', 'Karasuwa', 'Machina', 'Nangere', 'Nguru', 'Potiskum', 'Tarmuwa', 'Yunusari', 'Yusufari'] },
                    { name: 'Zamfara', lgas: ['Anka', 'Bakura', 'Birnin Magaji/Kiyaw', 'Bukkuyum', 'Bungudu', 'Gummi', 'Gusau', 'Kaura Namoda', 'Maradun', 'Maru', 'Shinkafi', 'Talata Mafara', 'Chafe', 'Zurmi'] }
                ],
                selectedState: '{{ old('address_state') }}',
                selectedLga: '{{ old('address_lga') }}',
                lgas: [],

                init() {
                    if (this.selectedState) {
                        this.updateLgas();
                    }
                },

                updateLgas() {
                    const stateObj = this.states.find(s => s.name === this.selectedState);
                    this.lgas = stateObj ? stateObj.lgas : [];
                    // Preserve old LGA on validation failure, otherwise reset
                    if (!this.lgas.includes(this.selectedLga)) {
                        this.selectedLga = '';
                    }
                }
            }));
        });
    </script>
</x-app-layout>
