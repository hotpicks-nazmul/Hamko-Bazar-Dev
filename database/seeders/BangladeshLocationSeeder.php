<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Area;

class BangladeshLocationSeeder extends Seeder
{
    public function run()
    {
        $bd = Country::where('code', 'BD')->first();
        if (!$bd) {
            $this->command->error('Bangladesh country not found. Run CountriesSeeder first.');
            return;
        }

        City::where('country_id', $bd->id)->each(function ($city) {
            $city->areas()->delete();
            $city->delete();
        });

        $divisionMap = [
            'Dhaka' => 'Dhaka',
            'Faridpur' => 'Dhaka', 'Gazipur' => 'Dhaka', 'Gopalganj' => 'Dhaka',
            'Kishoreganj' => 'Dhaka', 'Madaripur' => 'Dhaka', 'Manikganj' => 'Dhaka',
            'Munshiganj' => 'Dhaka', 'Narayanganj' => 'Dhaka', 'Narsingdi' => 'Dhaka',
            'Rajbari' => 'Dhaka', 'Shariatpur' => 'Dhaka', 'Tangail' => 'Dhaka',
            'Brahmanbaria' => 'Chattogram', 'Chandpur' => 'Chattogram', 'Chattogram' => 'Chattogram',
            'Cumilla' => 'Chattogram', "Cox's Bazar" => 'Chattogram', 'Feni' => 'Chattogram',
            'Bandarban' => 'Chattogram', 'Khagrachhari' => 'Chattogram', 'Lakshmipur' => 'Chattogram',
            'Noakhali' => 'Chattogram', 'Rangamati' => 'Chattogram',
            'Bagerhat' => 'Khulna', 'Chuadanga' => 'Khulna', 'Jessore' => 'Khulna',
            'Jhenaidah' => 'Khulna', 'Khulna' => 'Khulna', 'Kushtia' => 'Khulna',
            'Magura' => 'Khulna', 'Meherpur' => 'Khulna', 'Narail' => 'Khulna',
            'Satkhira' => 'Khulna',
            'Bogra' => 'Rajshahi', 'Chapai Nawabganj' => 'Rajshahi', 'Joypurhat' => 'Rajshahi',
            'Naogaon' => 'Rajshahi', 'Natore' => 'Rajshahi', 'Pabna' => 'Rajshahi',
            'Rajshahi' => 'Rajshahi', 'Sirajganj' => 'Rajshahi',
            'Barguna' => 'Barishal', 'Barishal' => 'Barishal', 'Bhola' => 'Barishal',
            'Jhalokati' => 'Barishal', 'Patuakhali' => 'Barishal', 'Pirojpur' => 'Barishal',
            'Habiganj' => 'Sylhet', 'Moulvibazar' => 'Sylhet', 'Sunamganj' => 'Sylhet',
            'Sylhet' => 'Sylhet',
            'Dinajpur' => 'Rangpur', 'Gaibandha' => 'Rangpur', 'Kurigram' => 'Rangpur',
            'Lalmonirhat' => 'Rangpur', 'Nilphamari' => 'Rangpur', 'Panchagarh' => 'Rangpur',
            'Rangpur' => 'Rangpur', 'Thakurgaon' => 'Rangpur',
            'Jamalpur' => 'Mymensingh', 'Mymensingh' => 'Mymensingh', 'Netrokona' => 'Mymensingh',
            'Sherpur' => 'Mymensingh',
        ];

        $states = State::where('country_id', $bd->id)->get()->keyBy('name');

        $districts = [
            'Dhaka' => ['Demra', 'Dhaka Cantonment', 'Dhanmondi', 'Gulshan', 'Hazaribagh', 'Kadamtali', 'Kafrul', 'Kamrangirchar', 'Khilgaon', 'Khilkhet', 'Kotwali', 'Lalbagh', 'Mirpur', 'Mohammadpur', 'Motijheel', 'New Market', 'Pallabi', 'Ramna', 'Sabujbagh', 'Shah Ali', 'Shahbagh', 'Sher-e-Bangla Nagar', 'Shyampur', 'Sutrapur', 'Tejgaon', 'Tejgaon Industrial', 'Uttara', 'Uttarkhan', 'Bimanbandar', 'Keraniganj', 'Nawabganj', 'Dohar', 'Savar', 'Dhamrai'],
            'Faridpur' => ['Faridpur Sadar', 'Alfadanga', 'Boalmari', 'Charbhadrasan', 'Madhukhali', 'Nagarkanda', 'Sadarpur', 'Saltha', 'Bhanga'],
            'Gazipur' => ['Gazipur Sadar', 'Kaliakair', 'Kaliganj', 'Kapasia', 'Sreepur', 'Tongi'],
            'Gopalganj' => ['Gopalganj Sadar', 'Kashiani', 'Kotalipara', 'Muksudpur', 'Tungipara'],
            'Kishoreganj' => ['Kishoreganj Sadar', 'Austagram', 'Bajitpur', 'Bhairab', 'Hossainpur', 'Itna', 'Karimganj', 'Katiadi', 'Kuliarchar', 'Mithamain', 'Nikli', 'Pakundia', 'Tarail'],
            'Madaripur' => ['Madaripur Sadar', 'Kalkini', 'Rajoir', 'Shibchar'],
            'Manikganj' => ['Manikganj Sadar', 'Daulatpur', 'Ghior', 'Harirampur', 'Saturia', 'Shivalaya', 'Singair'],
            'Munshiganj' => ['Munshiganj Sadar', 'Gazaria', 'Lohajang', 'Sirajdikhan', 'Sreenagar', 'Tongibari'],
            'Narayanganj' => ['Narayanganj Sadar', 'Araihazar', 'Bandar', 'Narayanganj Port', 'Rupganj', 'Sonargaon'],
            'Narsingdi' => ['Narsingdi Sadar', 'Belabo', 'Monohardi', 'Palash', 'Raipura', 'Shibpur'],
            'Rajbari' => ['Rajbari Sadar', 'Baliakandi', 'Goalandaghat', 'Kalukhali', 'Pangsha'],
            'Shariatpur' => ['Shariatpur Sadar', 'Bhedarganj', 'Damudya', 'Gosairhat', 'Jajira', 'Naria', 'Sakhipur'],
            'Tangail' => ['Tangail Sadar', 'Basail', 'Bhuapur', 'Delduar', 'Ghatail', 'Gopalpur', 'Kalihati', 'Madhupur', 'Mirzapur', 'Nagarpur', 'Sakhipur', 'Dhanbari'],
            'Brahmanbaria' => ['Brahmanbaria Sadar', 'Ashuganj', 'Bancharampur', 'Bijoynagar', 'Kasba', 'Akhaura', 'Nabinagar', 'Nasirnagar', 'Sarail'],
            'Chandpur' => ['Chandpur Sadar', 'Faridganj', 'Haimchar', 'Haziganj', 'Kachua', 'Matlab Dakshin', 'Matlab Uttar', 'Shahrasti'],
            'Chattogram' => ['Akbar Shah', 'Bakalia', 'Bandar', 'Bayezid Bostami', 'Chandgaon', 'Chawkbazar', 'Double Mooring', 'EPZ', 'Halishahar', 'Karnaphuli', 'Khulshi', 'Kotwali', 'Pahartali', 'Panchlaish', 'Patharghata', 'Sadarghat', 'Shah Mirpur'],
            'Cumilla' => ['Cumilla Sadar', 'Barura', 'Brahmanpara', 'Burichang', 'Chandina', 'Chouddagram', 'Daudkandi', 'Debidwar', 'Homna', 'Laksam', 'Lalmai', 'Manoharganj', 'Meghna', 'Muradnagar', 'Nangalkot', 'Titas'],
            'Cox\'s Bazar' => ['Cox\'s Bazar Sadar', 'Chakaria', 'Kutubdia', 'Maheshkhali', 'Pekua', 'Ramu', 'Teknaf', 'Ukhia'],
            'Feni' => ['Feni Sadar', 'Chhagalnaiya', 'Daganbhuiyan', 'Fulgazi', 'Parshuram', 'Sonagazi'],
            'Bandarban' => ['Bandarban Sadar', 'Alikadam', 'Naikhongchhari', 'Rowangchhari', 'Ruma', 'Lama', 'Thanchi'],
            'Khagrachhari' => ['Khagrachhari Sadar', 'Dighinala', 'Lakshmichhari', 'Mahalchhari', 'Manikchhari', 'Matiranga', 'Panchhari', 'Ramgarh'],
            'Lakshmipur' => ['Lakshmipur Sadar', 'Kamalnagar', 'Raipur', 'Ramganj', 'Ramgati'],
            'Noakhali' => ['Noakhali Sadar', 'Begumganj', 'Chatkhil', 'Companyganj', 'Hatiya', 'Kabirhat', 'Senbagh', 'Subarnachar'],
            'Rangamati' => ['Rangamati Sadar', 'Belaichhari', 'Barkal', 'Baghaichhari', 'Juraichhari', 'Kaptai', 'Kawkhali', 'Langadu', 'Naniyachar', 'Rajasthali'],
            'Bagerhat' => ['Bagerhat Sadar', 'Chitalmari', 'Fakirhat', 'Kachua', 'Mollahat', 'Mongla', 'Morrelganj', 'Rampal', 'Sarankhola'],
            'Chuadanga' => ['Chuadanga Sadar', 'Alamdanga', 'Damurhuda', 'Jibannagar'],
            'Jessore' => ['Jessore Sadar', 'Abhaynagar', 'Bagherpara', 'Chaugachha', 'Jhikargachha', 'Keshabpur', 'Manirampur', 'Sharsha'],
            'Jhenaidah' => ['Jhenaidah Sadar', 'Harinakunda', 'Kaliganj', 'Kotchandpur', 'Maheshpur', 'Shailkupa'],
            'Khulna' => ['Khulna Sadar', 'Batiaghata', 'Dacope', 'Dighalia', 'Dumuria', 'Koyra', 'Paikgachha', 'Phultala', 'Rupsha', 'Terokhada', 'Daulatpur', 'Khalishpur', 'Khan Jahan Ali', 'Sonadanga', 'Harintana'],
            'Kushtia' => ['Kushtia Sadar', 'Bheramara', 'Daulatpur', 'Khoksa', 'Kumarkhali', 'Mirpur'],
            'Magura' => ['Magura Sadar', 'Mohammadpur', 'Shalikha', 'Sreepur'],
            'Meherpur' => ['Meherpur Sadar', 'Gangni', 'Mujibnagar'],
            'Narail' => ['Narail Sadar', 'Kalia', 'Lohagara'],
            'Satkhira' => ['Satkhira Sadar', 'Assasuni', 'Debhata', 'Kalaroa', 'Kaliganj', 'Shyamnagar', 'Tala'],
            'Bogra' => ['Bogra Sadar', 'Adamdighi', 'Dhunat', 'Dhupchanchia', 'Gabtali', 'Kahaloo', 'Nandigram', 'Sariakandi', 'Shajahanpur', 'Sherpur', 'Shibganj', 'Sonatola'],
            'Chapai Nawabganj' => ['Chapai Nawabganj Sadar', 'Bholahat', 'Gomostapur', 'Nachole', 'Shibganj'],
            'Joypurhat' => ['Joypurhat Sadar', 'Akkelpur', 'Kalai', 'Khetlal', 'Panchbibi'],
            'Naogaon' => ['Naogaon Sadar', 'Atrai', 'Badalgachhi', 'Dhamoirhat', 'Manda', 'Mohadevpur', 'Niamatpur', 'Patnitala', 'Porsha', 'Raninagar', 'Sapahar'],
            'Natore' => ['Natore Sadar', 'Bagatipara', 'Baraigram', 'Gurudaspur', 'Lalpur', 'Naldanga', 'Singra'],
            'Pabna' => ['Pabna Sadar', 'Atgharia', 'Bera', 'Bhangura', 'Chatmohar', 'Faridpur', 'Ishwardi', 'Santhia', 'Sujanagar'],
            'Rajshahi' => ['Rajshahi Sadar', 'Bagha', 'Bagmara', 'Charghat', 'Durgapur', 'Godagari', 'Mohanpur', 'Paba', 'Puthia', 'Tanore', 'Boalia', 'Motihar', 'Shah Makhdum', 'Rajpara'],
            'Sirajganj' => ['Sirajganj Sadar', 'Belkuchi', 'Chauhali', 'Kamarkhanda', 'Kazipur', 'Raiganj', 'Shahjadpur', 'Tarash', 'Ullahpara'],
            'Barguna' => ['Barguna Sadar', 'Amtali', 'Bamna', 'Betagi', 'Patharghata', 'Taltali'],
            'Barishal' => ['Barishal Sadar', 'Agailjhara', 'Babuganj', 'Bakerganj', 'Banaripara', 'Gournadi', 'Hizla', 'Mehendiganj', 'Muladi', 'Wazirpur', 'Kotwali', 'Airport'],
            'Bhola' => ['Bhola Sadar', 'Borhanuddin', 'Charfesson', 'Daulatkhan', 'Lalmohan', 'Manpura', 'Tazumuddin'],
            'Jhalokati' => ['Jhalokati Sadar', 'Kathalia', 'Nalchity', 'Rajapur'],
            'Patuakhali' => ['Patuakhali Sadar', 'Bauphal', 'Dashmina', 'Dumki', 'Galachipa', 'Kalapara', 'Mirzaganj', 'Rangabali'],
            'Pirojpur' => ['Pirojpur Sadar', 'Bhandaria', 'Kawkhali', 'Mathbaria', 'Nazirpur', 'Nesarabad', 'Zianagar'],
            'Habiganj' => ['Habiganj Sadar', 'Ajmiriganj', 'Bahubal', 'Baniachong', 'Chunarughat', 'Lakhai', 'Madhabpur', 'Nabiganj', 'Shaistaganj'],
            'Moulvibazar' => ['Moulvibazar Sadar', 'Barlekha', 'Juri', 'Kamalganj', 'Kulaura', 'Rajnagar', 'Sreemangal'],
            'Sunamganj' => ['Sunamganj Sadar', 'Bishwambarpur', 'Chhatak', 'Derai', 'Dharampasha', 'Dowarabazar', 'Jagannathpur', 'Jamalganj', 'Sullah', 'Tahirpur'],
            'Sylhet' => ['Sylhet Sadar', 'Balaganj', 'Beanibazar', 'Bishwanath', 'Companiganj', 'Dakshin Surma', 'Fenchuganj', 'Golapganj', 'Gowainghat', 'Jaintiapur', 'Kanaighat', 'Osmani Nagar', 'Zakiganj', 'Jalalabad', 'Mogla Bazar', 'Khadimnagar', 'South Surma'],
            'Dinajpur' => ['Dinajpur Sadar', 'Birampur', 'Birganj', 'Biral', 'Bochaganj', 'Chirirbandar', 'Fulbari', 'Ghoraghat', 'Hakimpur', 'Kaharole', 'Khansama', 'Nawabganj', 'Parbatipur', 'Phulchari'],
            'Gaibandha' => ['Gaibandha Sadar', 'Fulchhari', 'Gobindaganj', 'Palashbari', 'Sadullapur', 'Sundarganj', 'Saghata'],
            'Kurigram' => ['Kurigram Sadar', 'Bhurungamari', 'Char Rajibpur', 'Chilmari', 'Phulbari', 'Nageshwari', 'Rajarhat', 'Raomari', 'Ulipur'],
            'Lalmonirhat' => ['Lalmonirhat Sadar', 'Aditmari', 'Hatibandha', 'Kaliganj', 'Patgram'],
            'Nilphamari' => ['Nilphamari Sadar', 'Dimla', 'Domar', 'Jaldhaka', 'Kishoreganj', 'Saidpur'],
            'Panchagarh' => ['Panchagarh Sadar', 'Atwari', 'Boda', 'Debiganj', 'Tetulia'],
            'Rangpur' => ['Rangpur Sadar', 'Badarganj', 'Gangachara', 'Kaunia', 'Mithapukur', 'Pirgachha', 'Pirganj', 'Taraganj'],
            'Thakurgaon' => ['Thakurgaon Sadar', 'Baliadangi', 'Haripur', 'Pirganj', 'Ranishankail'],
            'Jamalpur' => ['Jamalpur Sadar', 'Bakshiganj', 'Dewanganj', 'Islampur', 'Madarganj', 'Melandaha', 'Sarishabari'],
            'Mymensingh' => ['Mymensingh Sadar', 'Bhaluka', 'Dhobaura', 'Fulbaria', 'Gaffargaon', 'Gauripur', 'Haluaghat', 'Ishwarganj', 'Muktagachha', 'Nandail', 'Phulpur', 'Trishal', 'Tarakananda', 'Kotwali'],
            'Netrokona' => ['Netrokona Sadar', 'Atpara', 'Barhatta', 'Durgapur', 'Kalmakanda', 'Kendua', 'Khaliajuri', 'Madan', 'Mohanganj', 'Purbadhala'],
            'Sherpur' => ['Sherpur Sadar', 'Jhenaigati', 'Nakla', 'Nalitabari', 'Sreebardi'],
        ];

        foreach ($districts as $districtName => $upazilas) {
            $stateId = isset($divisionMap[$districtName], $states[$divisionMap[$districtName]])
                ? $states[$divisionMap[$districtName]]->id : null;
            $city = City::create([
                'name' => $districtName,
                'country_id' => $bd->id,
                'state_id' => $stateId,
                'country' => 'Bangladesh',
                'is_active' => true,
            ]);

            foreach ($upazilas as $upazilaName) {
                Area::create([
                    'name' => $upazilaName,
                    'city_id' => $city->id,
                    'is_active' => true,
                ]);
            }
        }

        $totalCities = City::where('country_id', $bd->id)->count();
        $totalAreas = Area::whereIn('city_id', City::where('country_id', $bd->id)->pluck('id'))->count();
        $this->command->info("Bangladesh: {$totalCities} districts (zila), {$totalAreas} upazilas seeded.");
    }
}
