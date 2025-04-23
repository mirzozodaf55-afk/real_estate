export default function getData(){
    const responce = useState('responce',()=>{})
    const announData = useState('announ',()=>[])
    const avatar = useState('avatar',()=>{});
    const AnnounOgj = useState('AnnounOgj', ()=>{})
    const rotation = useState('rotaote', () => 0)
    const images = useState('images', () => []);
    const isUpload = useState('upload', () => false)
    const formData = useState('formData',()=>[])
    const city = useState('city',() => '');
    const dataFormBack = useState('dataFormBack',()=>[])
    const direction = useState('direction',()=>'')
    const showAnnouns = useState('showAnnoun',()=>[])
    const typeObject = useState('typeObject', () => '')
    const quantityRoom = useState('quantityRoom',()=>[])
    const priceFrom = useState('priceFrom',()=>'')
    const priceTo = useState('priceTo',()=>'')
    const typeDeal = useState('typeDeal',()=>'')
    const updateAnnounData = useState('updateAnnounData',()=>{})
    const show_video = useState('show_video',()=>false)
    // const token = useState('token',()=>JSON.parse(localStorage.getItem('token'))) 
     
    return{
        show_video,
        updateAnnounData,
        typeDeal,
        priceFrom,
        priceTo,
        quantityRoom,
        formData,
        dataFormBack,
        isUpload,
        city,
        direction,
        rotation,
        responce,
        announData,
        showAnnouns,
        AnnounOgj,
        images,
        avatar,
        typeObject
    }
}